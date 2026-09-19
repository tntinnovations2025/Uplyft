<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TeacherPortalController extends Controller
{
    /**
     * Dashboard Home
     * Route: /teacher/dashboard
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'getStaffUrlPrefix')) {
            $expectedPrefix = $user->getStaffUrlPrefix();
            if ($expectedPrefix !== 'teacher' && ($request->is('teacher') || $request->is('teacher/*'))) {
                return redirect($user->dashboardRoute());
            }
        }
        $instituteId = $user->institute_id;

        $activeTerm = AcademicTerm::getActiveTerm($instituteId);

        // 1. Fetch real Timetable slots assigned to this teacher from Principal/Admin Portal
        $timetableQuery = Timetable::with(['subject', 'section.instituteClass', 'room']);
        if ($activeTerm) {
            $timetableQuery->where('academic_term_id', $activeTerm->id);
        }
        $rawSlots = $timetableQuery->where('teacher_id', $user->id)->get();
        $mergedSlots = Timetable::mergeContiguousSlots($rawSlots);

        // Check if Principal/Admin has generated or stored any timetable slots in the DB
        $hasAnyInstituteTimetables = Timetable::where(function ($q) use ($activeTerm) {
            if ($activeTerm) {
                $q->where('academic_term_id', $activeTerm->id);
            }
        })->exists();

        // If no DB slots exist for this teacher, return empty collection (no fake fallbacks)
        if ($mergedSlots->isEmpty()) {
            $slotsData = collect();
        } else {
            $colors = ['cyan', 'indigo', 'emerald', 'amber', 'purple'];
            $colorIdx = 0;
            $subjectColors = [];

            $slotsData = $mergedSlots->map(function ($slot) use (&$colors, &$colorIdx, &$subjectColors) {
                $subCode = $slot->subject->subject_code ?? $slot->subject->subject_name ?? 'SUB-101';
                if (!isset($subjectColors[$subCode])) {
                    $subjectColors[$subCode] = $colors[$colorIdx % count($colors)];
                    $colorIdx++;
                }

                $formattedStart = date('h:i A', strtotime($slot->start_time));
                $formattedEnd = date('h:i A', strtotime($slot->end_time));

                $className = $slot->section->instituteClass->custom_name ?? '';
                $secName = $slot->section->section_name ?? '';
                $fullSection = $className ? ($secName ? "{$className} — Sec {$secName}" : $className) : ($secName ? "Section {$secName}" : 'Class Assigned');

                $startSec = strtotime($slot->start_time);
                $endSec = strtotime($slot->end_time);
                $diffMins = max(15, round(($endSec - $startSec) / 60));

                return [
                    'day' => strtolower($slot->day_of_week),
                    'start_time' => $formattedStart,
                    'end_time' => $formattedEnd,
                    'time_range' => "{$formattedStart} – {$formattedEnd}",
                    'duration_mins' => $diffMins,
                    'subject_name' => $slot->subject->subject_name ?? 'Subject',
                    'subject_code' => $subCode,
                    'room_name' => $slot->room ? $slot->formatted_room_name : ($fullSection ?: 'Main Hall'),
                    'section_name' => $fullSection,
                    'color_theme' => $subjectColors[$subCode],
                ];
            });
        }

        // Today's classes strictly for today's day of the week
        $todayDay = strtolower(now()->format('l'));
        $todaySlots = $slotsData->where('day', $todayDay)->values();

        // Weekly schedule grid mapping
        $timeSlotsList = $slotsData->pluck('start_time')->unique()->values();
        if ($timeSlotsList->isEmpty()) {
            $timeSlotsList = collect();
        }

        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        $weeklyGrid = [];
        foreach ($timeSlotsList as $tSlot) {
            foreach ($daysOfWeek as $day) {
                $found = $slotsData->first(function ($item) use ($tSlot, $day) {
                    return $item['day'] === $day && $item['start_time'] === $tSlot;
                });
                $weeklyGrid[$tSlot][$day] = $found;
            }
        }

        // Real pending assignments count for this teacher
        $pendingSubmissionsCount = \App\Models\AssessmentSubmission::whereIn('status', [\App\Models\AssessmentSubmission::STATUS_COMPLETED, \App\Models\AssessmentSubmission::STATUS_AUTO_SUBMITTED])
            ->whereNull('total_score')
            ->whereHas('assessment', function ($q) use ($user) {
                $q->where('creator_id', $user->id)->orWhere('teacher_id', $user->id);
            })->count();

        // Fetch real financial metrics for accountants and staff with accounting permissions
        $isAccountant = strtolower($user->staff_role ?? '') === 'accountant' || ($user->hasPermission('accounts') && !$user->hasPermission('attendance'));

        $showFinancials = $isAccountant || $user->hasPermission('accounts') || $user->hasPermission('invoices');

        $totalFeeCollected = 0;
        $totalRemainingFee = 0;
        $totalFinesCollected = 0;
        $paidCount = 0;
        $unpaidCount = 0;
        $monthsList = [];
        $monthlyCollectedData = [];
        $monthlyUnpaidData = [];

        // Heavy financial aggregations are strictly scoped to the active institute
        if ($showFinancials) {
            $financialMetrics = \Illuminate\Support\Facades\Cache::remember("teacher_dash_finance_{$instituteId}", 30, function () use ($instituteId) {
                $totalFeeCollected = (float) \App\Models\Invoice::where('institute_id', $instituteId)->where('status', 'paid')->sum('amount_pkr');
                $totalRemainingFee = (float) \App\Models\Invoice::where('institute_id', $instituteId)->where('status', 'unpaid')->sum('amount_pkr');
                $totalFinesCollected = (float) \App\Models\FinancialTransaction::where('institute_id', $instituteId)
                    ->where('type', 'income')
                    ->whereHas('accountHead', function ($q) {
                        $q->where('name', 'like', '%Fine%')
                          ->orWhere('name', 'like', '%Penalty%')
                          ->orWhere('name', 'like', '%Late%');
                    })->sum('amount');

                // Detailed Financial Analytics Data
                $paidCount = \App\Models\Invoice::where('institute_id', $instituteId)->where('status', 'paid')->count();
                $unpaidCount = \App\Models\Invoice::where('institute_id', $instituteId)->where('status', 'unpaid')->count();

                // 6-Month Fee & Income Collection Trends
                $monthsList = [];
                $monthlyCollectedData = [];
                $monthlyUnpaidData = [];
                for ($i = 5; $i >= 0; $i--) {
                    $monthDate = now()->subMonths($i);
                    $monthLabel = $monthDate->format('M Y');
                    $monthsList[] = $monthLabel;

                    $startOfMonth = $monthDate->copy()->startOfMonth();
                    $endOfMonth = $monthDate->copy()->endOfMonth();

                    $collected = \App\Models\Invoice::where('institute_id', $instituteId)
                        ->where('status', 'paid')
                        ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                        ->sum('amount_pkr');

                    $unpaid = \App\Models\Invoice::where('institute_id', $instituteId)
                        ->where('status', 'unpaid')
                        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->sum('amount_pkr');

                    $monthlyCollectedData[] = (float) $collected;
                    $monthlyUnpaidData[] = (float) $unpaid;
                }

                return compact('totalFeeCollected', 'totalRemainingFee', 'totalFinesCollected', 'paidCount', 'unpaidCount', 'monthsList', 'monthlyCollectedData', 'monthlyUnpaidData');
            });

            $totalFeeCollected = $financialMetrics['totalFeeCollected'];
            $totalRemainingFee = $financialMetrics['totalRemainingFee'];
            $totalFinesCollected = $financialMetrics['totalFinesCollected'];
            $paidCount = $financialMetrics['paidCount'];
            $unpaidCount = $financialMetrics['unpaidCount'];
            $monthsList = $financialMetrics['monthsList'];
            $monthlyCollectedData = $financialMetrics['monthlyCollectedData'];
            $monthlyUnpaidData = $financialMetrics['monthlyUnpaidData'];
        }

        return view('teacher.dashboard', compact(
            'user', 'todaySlots', 'slotsData', 'timeSlotsList', 'daysOfWeek', 'weeklyGrid', 'activeTerm',
            'isAccountant', 'totalFeeCollected', 'totalRemainingFee', 'totalFinesCollected',
            'paidCount', 'unpaidCount', 'monthsList', 'monthlyCollectedData', 'monthlyUnpaidData',
            'pendingSubmissionsCount'
        ));
    }

    /**
     * My Schedule
     * Route: /teacher/schedule
     */
    public function schedule()
    {
        $user = Auth::user();
        $instituteId = $user->institute_id;

        $activeTerm = AcademicTerm::getActiveTerm($instituteId);

        $timetableQuery = Timetable::with(['subject', 'section.instituteClass', 'room']);
        if ($activeTerm) {
            $timetableQuery->where('academic_term_id', $activeTerm->id);
        }
        $rawSlots = $timetableQuery->where('teacher_id', $user->id)->get();
        $mergedSlots = Timetable::mergeContiguousSlots($rawSlots);

        $hasAnyInstituteTimetables = Timetable::where(function ($q) use ($activeTerm) {
            if ($activeTerm) {
                $q->where('academic_term_id', $activeTerm->id);
            }
        })->exists();

        if ($mergedSlots->isEmpty()) {
            $slotsData = collect();
        } else {
            $colors = ['cyan', 'indigo', 'emerald', 'amber', 'purple'];
            $colorIdx = 0;
            $subjectColors = [];

            $slotsData = $mergedSlots->map(function ($slot) use (&$colors, &$colorIdx, &$subjectColors) {
                $subCode = $slot->subject->subject_code ?? $slot->subject->subject_name ?? 'SUB-101';
                if (!isset($subjectColors[$subCode])) {
                    $subjectColors[$subCode] = $colors[$colorIdx % count($colors)];
                    $colorIdx++;
                }

                $formattedStart = date('h:i A', strtotime($slot->start_time));
                $formattedEnd = date('h:i A', strtotime($slot->end_time));

                $className = $slot->section->instituteClass->custom_name ?? '';
                $secName = $slot->section->section_name ?? '';
                $fullSection = $className ? ($secName ? "{$className} — Sec {$secName}" : $className) : ($secName ? "Section {$secName}" : 'Class Assigned');

                $startSec = strtotime($slot->start_time);
                $endSec = strtotime($slot->end_time);
                $diffMins = max(15, round(($endSec - $startSec) / 60));

                return [
                    'day' => strtolower($slot->day_of_week),
                    'start_time' => $formattedStart,
                    'end_time' => $formattedEnd,
                    'time_range' => "{$formattedStart} – {$formattedEnd}",
                    'duration_mins' => $diffMins,
                    'subject_name' => $slot->subject->subject_name ?? 'Subject',
                    'subject_code' => $subCode,
                    'room_name' => $slot->room ? $slot->formatted_room_name : ($fullSection ?: 'Main Hall'),
                    'section_name' => $fullSection,
                    'color_theme' => $subjectColors[$subCode],
                ];
            });
        }

        $timeSlotsList = $slotsData->pluck('start_time')->unique()->values();
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        $weeklyGrid = [];
        foreach ($timeSlotsList as $tSlot) {
            foreach ($daysOfWeek as $day) {
                $found = $slotsData->first(function ($item) use ($tSlot, $day) {
                    return $item['day'] === $day && $item['start_time'] === $tSlot;
                });
                $weeklyGrid[$tSlot][$day] = $found;
            }
        }

        return view('teacher.schedule', compact('user', 'slotsData', 'timeSlotsList', 'daysOfWeek', 'weeklyGrid', 'activeTerm'));
    }

    /**
     * Mark Attendance (Connect to Module 5)
     * Route: /teacher/attendance
     */
    /**
     * Mark Attendance (Restricted strictly to assigned classes & window time)
     * Route: /teacher/attendance
     */
    public function attendance(Request $request)
    {
        $user = Auth::user();
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $setting = AttendanceSetting::getForInstitute($user->institute_id);
        $attendanceMode = $setting->attendance_mode ?? 'daily';

        $inchargeNotice = null;

        // 1. Fetch accessible sections based on Attendance Mode & User Role
        if ($user->isPrincipal() || $user->is_delegated_admin) {
            $sections = ClassSection::whereHas('instituteClass', function ($q) use ($user) {
                    $q->where('institute_id', $user->institute_id);
                })
                ->with(['instituteClass', 'classIncharge'])
                ->get();
        } else {
            if ($attendanceMode === 'daily') {
                // In Daily mode: ONLY sections where this teacher is assigned as Class Incharge
                $sections = ClassSection::with(['instituteClass', 'classIncharge'])
                    ->where('class_incharge_id', $user->id)
                    ->get();

                if ($sections->isEmpty()) {
                    $inchargeNotice = '🔒 Daily Attendance Mode Active: Attendance marking is restricted to designated Class Incharge teachers. You are currently not assigned as Class Incharge for any section.';
                }
            } else {
                // In Subject mode: Sections assigned to this teacher in teacher_subject_sections
                $assignedSectionIds = TeacherSubjectSection::where('teacher_id', $user->id)
                    ->pluck('class_section_id')
                    ->unique();

                $sections = ClassSection::with(['instituteClass', 'classIncharge'])
                    ->whereIn('id', $assignedSectionIds)
                    ->get();
            }
        }

        // Sort sections in sequential academic order (e.g. Class 1, Class 2, ... Section A, B)
        $sections = $sections->sortBy(function ($sec) {
            $cName = $sec->instituteClass->custom_name ?? '';
            preg_match('/\d+/', $cName, $m);
            $num = isset($m[0]) ? (int)$m[0] : 999;
            return sprintf('%03d_%s_%s', $num, $cName, $sec->section_name);
        })->values();

        $selectedSectionId = $request->input('section_id');
        if (!$selectedSectionId && $sections->isNotEmpty()) {
            $selectedSectionId = $sections->first()->id;
        }

        $selectedSection = $sections->firstWhere('id', (int) $selectedSectionId);

        // Security check: if teacher attempts to access unauthorized section
        if (!$user->isPrincipal() && !$user->is_delegated_admin && $selectedSectionId && !$sections->pluck('id')->contains((int) $selectedSectionId)) {
            abort(403, 'Unauthorized access: You are only authorized to mark attendance for classes assigned to you.');
        }

        // 2. Evaluate Attendance Lock Status (Principal / Delegated Admin always bypasses lock)
        $lockStatus = $setting->getLockStatusForDate($selectedDate);
        if ($user->isPrincipal() || $user->is_delegated_admin) {
            $lockStatus['is_locked'] = false;
        }

        // 3. Load Student Roster, Term Stats, and Daily Attendance Logs
        $students = collect();
        $attendances = collect();

        if ($selectedSectionId) {
            $students = Student::where('class_section_id', $selectedSectionId)
                ->orderBy('roll_number')
                ->orderBy('first_name')
                ->get();

            $activeTerm = AcademicTerm::getActiveTerm($user->institute_id);

            $attendances = Attendance::whereDate('date', $selectedDate)
                ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');

            // Calculate term attendance statistics for each student
            $allTermLogs = Attendance::whereIn('student_id', $students->pluck('id'))
                ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
                ->get()
                ->groupBy('student_id');

            foreach ($students as $student) {
                $sLogs = $allTermLogs->get($student->id, collect());
                $totalSessions = $sLogs->count();
                $presentSessions = $sLogs->filter(fn ($l) => in_array(strtolower($l->status), ['present', 'late']))->count();
                $student->attendance_total_sessions = $totalSessions;
                $student->attendance_present_sessions = $presentSessions;
                $student->attendance_percentage = $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100, 1) : 100;
            }
        }

        return view('teacher.attendance', compact('user', 'sections', 'selectedSectionId', 'selectedSection', 'students', 'attendances', 'selectedDate', 'lockStatus', 'setting', 'attendanceMode', 'inchargeNotice'));
    }

    /**
     * Store Daily Attendance
     * Route: POST /teacher/attendance
     */
    public function storeAttendance(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'section_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'string', 'in:present,absent,late,leave,Present,Absent,Late,Leave'],
            'leave_reason' => ['nullable', 'array'],
        ]);

        $sectionId = (int) $validated['section_id'];
        $date = $validated['date'];

        $instituteId = $user->getActiveInstituteId();

        if ($instituteId === null) {
            return redirect()->back()->with('error', '⚠️ Unable to resolve your institute context.');
        }

        $setting = AttendanceSetting::getForInstitute($instituteId);
        $attendanceMode = $setting->attendance_mode ?? 'daily';
        $section = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId) {
            $q->where('institute_id', $instituteId);
        })->find($sectionId);

        // 1. Security Check: Enforce Role & Mode Authorization
        if (!$user->isPrincipal()) {
            if ($attendanceMode === 'daily') {
                if (!$section || $section->class_incharge_id !== $user->id) {
                    return redirect()->back()->with('error', '⚠️ Access Denied: In Daily Attendance mode, only the designated Class Incharge teacher can mark attendance.');
                }
            } else {
                $isAssigned = TeacherSubjectSection::where('teacher_id', $user->id)
                    ->where('class_section_id', $sectionId)
                    ->exists();

                if (!$isAssigned) {
                    return redirect()->back()->with('error', '⚠️ Access Denied: You are not assigned to mark attendance for this subject/class section.');
                }
            }
        }

        // 2. Lock Check: Attendance Time Window & Edit Lock
        $lockStatus = $setting->getLockStatusForDate($date);

        if ($lockStatus['is_locked']) {
            return redirect()->back()->with('error', "🔒 Attendance Locked: {$lockStatus['reason']}");
        }

        // 2b. Section must belong to the institute and be found
        if (!$section) {
            return redirect()->back()->with('error', '⚠️ Invalid class section for your institute.');
        }

        // 3. Save Attendance Entries
        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (!$activeTerm) {
            return redirect()->back()->with('error', '⚠️ No active academic term is configured for your institute.');
        }

        // Whitelist: only students enrolled in this class section may be marked
        $rosterStudentIds = Student::withoutGlobalScopes()
            ->where('institute_id', $instituteId)
            ->where('class_section_id', $sectionId)
            ->pluck('id')
            ->flip();

        $processed = 0;
        foreach ($validated['status'] as $studentId => $statusVal) {
            if (!isset($rosterStudentIds[(int) $studentId])) {
                continue;
            }

            Attendance::updateOrCreate(
                [
                    'institute_id' => $instituteId,
                    'academic_term_id' => $activeTerm->id,
                    'student_id' => (int) $studentId,
                    'date' => $date,
                ],
                [
                    'status' => strtolower($statusVal),
                ]
            );
            $processed++;

            // Dispatch student portal notification
            try {
                $studentModel = Student::withoutGlobalScopes()->find((int) $studentId);
                if ($studentModel) {
                    \App\Services\PortalNotificationService::notifyStudentAttendanceMarked(
                        $studentModel,
                        strtolower($statusVal),
                        $date,
                        $section->section_name
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning("Could not dispatch student attendance notification: " . $e->getMessage());
            }
        }

        if ($processed > 0 && $section) {
            // Dispatch Real-Time Reverb Notification to Principal & Admins
            \App\Services\PrincipalNotificationService::notifyAttendanceMarked($instituteId, $section, $processed, $date, $user);
        }

        return redirect()->back()->with('success', "✅ Attendance successfully recorded for {$processed} student(s) on {$date}.");
    }

    /**
     * LMS & Exams (Placeholder for Module 6)
     * Route: /teacher/lms
     */
    public function lms()
    {
        return view('teacher.lms');
    }
}
