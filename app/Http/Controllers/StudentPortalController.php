<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\InstituteSetting;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    /**
     * Modern Student Executive Dashboard
     * Route: /student/dashboard
     */
    public function dashboard()
    {
        $student = $this->getStudentForAuthUser();

        if (!$student) {
            abort(403, 'No student profile is linked to your account. Please contact your institute for assistance.');
        }

        $instituteId = (Auth::check() && Auth::user()->institute_id) ? Auth::user()->institute_id : ($student->institute_id ?? 1);
        $instituteSetting = InstituteSetting::getForInstitute($instituteId);
        $attendanceMode = $instituteSetting->attendance_mode ?? 'subject'; // 'subject' or 'daily'
        $minAttendancePct = (float) ($instituteSetting->min_required_attendance_pct ?? 75.0);

        // 1. Attendance Computations
        $attendances = collect();
        if ($student->id) {
            $attendances = Attendance::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->orderBy('date', 'desc')
                ->get();
        }

        $totalDays = $attendances->count();
        $presentDays = $attendances->filter(fn ($a) => in_array(strtolower($a->status), ['present', 'late']))->count();
        $absentDays = $attendances->filter(fn ($a) => in_array(strtolower($a->status), ['absent']))->count();
        $leaveDays = $attendances->filter(fn ($a) => in_array(strtolower($a->status), ['leave', 'excused']))->count();

        $overallAttendancePct = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0.0;

        // Dynamic Subject Architecture: Scope to student's enrolled subjects
        $enrolledSubjectIds = \App\Models\StudentSubjectEnrollment::where('student_id', $student->user_id ?? Auth::id())
            ->where('enrollment_status', 'active')
            ->pluck('subject_id')
            ->all();

        // Subject-wise attendance calculation
        $subjectAttendance = collect();
        if ($student && $student->class_section_id) {
            $allocations = TeacherSubjectSection::where('class_section_id', $student->class_section_id)
                ->with(['subject', 'teacher'])
                ->get();

            $subjects = $allocations->pluck('subject')->filter()->unique('id');
            if ($subjects->isEmpty() && $student->classSection) {
                $subjects = Subject::where('institute_class_id', $student->classSection->institute_class_id)->get();
            }

            if (! empty($enrolledSubjectIds)) {
                $subjects = $subjects->whereIn('id', $enrolledSubjectIds)->values();
            }

            $idx = 0;
            foreach ($subjects as $subject) {
                $subLogs = $attendances->where('subject_id', $subject->id);
                if ($subLogs->isNotEmpty()) {
                    $subTotal = $subLogs->count();
                    $subPresents = $subLogs->filter(fn ($a) => in_array(strtolower($a->status), ['present', 'late']))->count();
                    $subPct = $subTotal > 0 ? round(($subPresents / $subTotal) * 100, 1) : 0.0;
                } elseif ($attendanceMode === 'daily' && $totalDays > 0) {
                    $subTotal = $totalDays;
                    $subPresents = $presentDays;
                    $subPct = $overallAttendancePct;
                } else {
                    $subTotal = 0;
                    $subPresents = 0;
                    $subPct = 0.0;
                }

                if ($subTotal === 0) {
                    $colorHex = '#68665D';
                    $bgClass = 'bg-slate-400';
                    $badgeClass = 'bg-slate-100 text-slate-700 border-slate-300';
                    $statusLabel = 'No Logs';
                } elseif ($subPct >= 85) {
                    $colorHex = '#10b981';
                    $bgClass = 'bg-emerald-500';
                    $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    $statusLabel = 'Excellent';
                } elseif ($subPct >= $minAttendancePct) {
                    $colorHex = '#6366f1';
                    $bgClass = 'bg-indigo-500';
                    $badgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                    $statusLabel = 'Good Standing';
                } elseif ($subPct >= 60) {
                    $colorHex = '#f59e0b';
                    $bgClass = 'bg-amber-500';
                    $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
                    $statusLabel = 'Warning';
                } else {
                    $colorHex = '#f43f5e';
                    $bgClass = 'bg-rose-500';
                    $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                    $statusLabel = 'Critical Risk';
                }

                $subjectAttendance->push([
                    'id' => $subject->id,
                    'name' => $subject->subject_name,
                    'code' => $subject->subject_code ?? 'SUB-' . ($idx + 1),
                    'total' => $subTotal,
                    'presents' => $subPresents,
                    'percentage' => $subPct,
                    'color_hex' => $colorHex,
                    'bg_class' => $bgClass,
                    'badge_class' => $badgeClass,
                    'status_label' => $statusLabel,
                ]);

                $idx++;
            }
        }

        // Daily Attendance Gauge Color
        if ($totalDays === 0) {
            $gaugeColor = '#68665D';
            $gaugeBadge = 'bg-slate-100 text-slate-700 border-slate-300';
            $gaugeStatus = 'No Records Yet';
        } elseif ($overallAttendancePct >= 85) {
            $gaugeColor = '#10b981';
            $gaugeBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            $gaugeStatus = 'Excellent Standing';
        } elseif ($overallAttendancePct >= $minAttendancePct) {
            $gaugeColor = '#6366f1';
            $gaugeBadge = 'bg-indigo-50 text-indigo-700 border-indigo-200';
            $gaugeStatus = 'Good Standing';
        } elseif ($overallAttendancePct >= 60) {
            $gaugeColor = '#f59e0b';
            $gaugeBadge = 'bg-amber-50 text-amber-700 border-amber-200';
            $gaugeStatus = 'Warning: Attendance Low';
        } else {
            $gaugeColor = '#f43f5e';
            $gaugeBadge = 'bg-rose-50 text-rose-700 border-rose-200';
            $gaugeStatus = 'Critical Attendance Risk';
        }

        // 2. Comprehensive Fee / Dues Analysis
        $unpaidInvoices = collect();
        $paidInvoices = collect();
        if ($student->id) {
            $unpaidInvoices = Invoice::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->where('status', '!=', 'paid')
                ->orderBy('due_date', 'asc')
                ->get();

            $paidInvoices = Invoice::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->where('status', 'paid')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $hasPendingFee = $unpaidInvoices->isNotEmpty() && $unpaidInvoices->sum('amount_pkr') > 0;
        $totalPendingAmount = (float) $unpaidInvoices->sum('amount_pkr');
        $primaryPendingInvoice = $unpaidInvoices->first();

        $daysRemainingText = '';
        $daysRemainingClass = '';
        $isOverdue = false;
        $formattedDueDate = '';

        if ($hasPendingFee && $primaryPendingInvoice) {
            $dueDate = $primaryPendingInvoice->due_date ? \Carbon\Carbon::parse($primaryPendingInvoice->due_date)->startOfDay() : now()->addDays(7)->startOfDay();
            $today = now()->startOfDay();
            $diffDays = (int) $today->diffInDays($dueDate, false);

            if ($diffDays < 0) {
                $isOverdue = true;
                $daysRemainingText = 'Overdue by ' . abs($diffDays) . ' ' . (abs($diffDays) == 1 ? 'day' : 'days');
                $daysRemainingClass = 'bg-rose-100 text-rose-800 border-rose-300';
            } elseif ($diffDays === 0) {
                $daysRemainingText = 'Due Today';
                $daysRemainingClass = 'bg-amber-100 text-amber-800 border-amber-300';
            } elseif ($diffDays === 1) {
                $daysRemainingText = '1 day remaining';
                $daysRemainingClass = 'bg-rose-50 text-rose-700 border-rose-200';
            } else {
                $daysRemainingText = $diffDays . ' days remaining';
                $daysRemainingClass = 'bg-rose-50 text-rose-700 border-rose-200';
            }
            $formattedDueDate = $dueDate->format('M d, Y');
        } else {
            $latestPaid = $paidInvoices->first();
            $formattedDueDate = $latestPaid && $latestPaid->due_date ? \Carbon\Carbon::parse($latestPaid->due_date)->format('M d, Y') : now()->format('M d, Y');
            $daysRemainingText = 'All Clear';
            $daysRemainingClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        }

        // 3. Today's Classes & Timetable
        $todayDay = strtolower(now()->format('l')); // 'monday', etc.
        $todaySlots = collect();

        if ($student->class_section_id) {
            $rawToday = Timetable::where('class_section_id', $student->class_section_id)
                ->whereRaw('LOWER(day_of_week) = ?', [$todayDay])
                ->with(['subject', 'teacher', 'room'])
                ->orderBy('start_time')
                ->get();

            if (! empty($enrolledSubjectIds)) {
                $rawToday = $rawToday->filter(fn ($slot) => in_array($slot->subject_id, $enrolledSubjectIds));
            }

            $todaySlots = Timetable::mergeContiguousSlots($rawToday);
        }

        // 4. Upcoming Deadlines / LMS Assessments
        $upcomingAssessments = collect();
        try {
            if ($student->classSection) {
                $classId = $student->classSection->institute_class_id;
                $assessQuery = Assessment::where('institute_class_id', $classId)
                    ->where(function ($q) {
                        $q->whereNull('due_date')->orWhere('due_date', '>=', now()->toDateString());
                    })
                    ->with('subject')
                    ->orderBy('due_date');

                if (! empty($enrolledSubjectIds)) {
                    $assessQuery->whereIn('subject_id', $enrolledSubjectIds);
                }

                $upcomingAssessments = $assessQuery->take(3)->get();
            }
        } catch (\Throwable $e) {
            $upcomingAssessments = collect();
        }

        return view('student.dashboard', compact(
            'student',
            'attendanceMode',
            'overallAttendancePct',
            'totalDays',
            'presentDays',
            'absentDays',
            'leaveDays',
            'subjectAttendance',
            'gaugeColor',
            'gaugeBadge',
            'gaugeStatus',
            'minAttendancePct',
            'hasPendingFee',
            'totalPendingAmount',
            'primaryPendingInvoice',
            'daysRemainingText',
            'daysRemainingClass',
            'isOverdue',
            'formattedDueDate',
            'todaySlots',
            'todayDay',
            'upcomingAssessments'
        ));
    }

    /**
     * My Fee Ledger (Redirects to Invoices & Vouchers)
     * Route: /student/fees
     */
    public function fees()
    {
        return redirect()->route('student.invoices');
    }

    /**
     * Helper to resolve logged-in user to exact Student model
     */
    protected function getStudentForAuthUser()
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // 1. Check user_id relationship or query
        $student = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->with(['classSection.instituteClass'])
            ->first();

        // 2. Check email match if student user_id wasn't linked (scoped to user's institute)
        if (!$student && !empty($user->email)) {
            $student = Student::withoutGlobalScopes()
                ->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
                ->when(!empty($user->institute_id), fn ($q) => $q->where('institute_id', $user->institute_id))
                ->with(['classSection.instituteClass'])
                ->first();
        }

        if ($student) {
            $updates = [];
            if (empty($student->user_id)) {
                $updates['user_id'] = $user->id;
                $student->user_id = $user->id;
            }
            if (empty($student->institute_id) && !empty($user->institute_id)) {
                $updates['institute_id'] = $user->institute_id;
                $student->institute_id = $user->institute_id;
            }
            if (!empty($updates)) {
                \Illuminate\Support\Facades\DB::table('students')
                    ->where('id', $student->id)
                    ->update($updates);
            }
        }

        return $student;
    }

    /**
     * Attendance Record (Connect to Module 5)
     * Route: /student/attendance
     */
    public function attendance()
    {
        $student = $this->getStudentForAuthUser();
        $attendances = collect();

        try {
            if ($student && isset($student->id)) {
                $academicTermId = request('term_id', 1);
                $attendances = Attendance::withoutGlobalScopes()
                    ->where('student_id', $student->id)
                    ->orderBy('date', 'desc')
                    ->get();
            }
        } catch (\Throwable $e) {
            $attendances = collect();
        }

        if (! $student) {
            abort(403, 'No student profile is linked to your account. Please contact your institute for assistance.');
        }

        $totalDays = $attendances->count();
        $presentDays = $attendances->filter(fn ($a) => in_array(strtolower($a->status), ['present', 'late']))->count();
        $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0.0;

        // Subject-wise percentage breakdown
        $subjectWiseAttendance = collect();
        if ($student && $student->class_section_id) {
            $allocations = \App\Models\TeacherSubjectSection::where('class_section_id', $student->class_section_id)
                ->with('subject')
                ->get();

            $subjects = $allocations->pluck('subject')->filter()->unique('id');
            if ($subjects->isEmpty() && $student->classSection) {
                $subjects = \App\Models\Subject::where('institute_class_id', $student->classSection->institute_class_id)->get();
            }

            foreach ($subjects as $subject) {
                $subLogs = $attendances->where('subject_id', $subject->id);
                if ($subLogs->isNotEmpty()) {
                    $subTotal = $subLogs->count();
                    $subPresents = $subLogs->filter(fn ($a) => in_array(strtolower($a->status), ['present', 'late']))->count();
                    $subPct = $subTotal > 0 ? round(($subPresents / $subTotal) * 100, 1) : 0.0;
                } else {
                    $subTotal = $totalDays;
                    $subPresents = $presentDays;
                    $subPct = $subTotal > 0 ? round(($subPresents / $subTotal) * 100, 1) : 0.0;
                }

                $subjectWiseAttendance->push([
                    'subject_name' => $subject->subject_name,
                    'subject_code' => $subject->subject_code ?? 'SUB',
                    'total_sessions' => $subTotal,
                    'present_sessions' => $subPresents,
                    'percentage' => $subPct,
                ]);
            }
        }

        return view('student.attendance', compact('student', 'attendances', 'totalDays', 'presentDays', 'percentage', 'subjectWiseAttendance'));
    }

    /**
     * Fee Invoices & Vouchers
     * Route: /student/invoices
     */
    public function invoices()
    {
        $student = $this->getStudentForAuthUser();

        if (! $student) {
            abort(403, 'No student profile is linked to your account. Please contact your institute for assistance.');
        }

        $invoices = collect();
        if ($student->id) {
            $student->load(['classSection.instituteClass']);
            $invoices = Invoice::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->with(['classSection.instituteClass'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Determine Base Fee from Student Profile or Issued Invoices
        $baseFee = (float) ($student->base_fee > 0 ? $student->base_fee : ($invoices->first()?->amount_pkr ?? 50000));

        // Admission & Security Fees are strictly ONE-TIME charges on initial registration.
        // For ongoing monthly fee calculations (after initial admission), set admission and security fee to 0.0.
        $hasSubsequentInvoices = $invoices->count() > 1;
        $admissionFee = $hasSubsequentInvoices ? 0.0 : (float) ($student->admission_fee ?? 0);
        $securityFee = $hasSubsequentInvoices ? 0.0 : (float) ($student->security_fee ?? 0);

        // Scholarship discount calculation
        $scholarshipPercentage = (float) ($student->scholarship_percentage ?? 0);
        $scholarshipName = $student->scholarship_name ?? null;
        $scholarshipDiscount = ($baseFee * $scholarshipPercentage) / 100;
        $netFeeAfterDiscount = max(0, $baseFee - $scholarshipDiscount);

        // FBR Guardian Tax calculation
        $isFiler = strtolower($student->guardian_tax_status ?? 'filer') === 'filer';
        $taxPercentage = (float) ($student->tax_percentage ?? ($isFiler ? 0 : 5));
        $taxableAmount = $netFeeAfterDiscount + $admissionFee;
        $taxAmount = ($taxableAmount * $taxPercentage) / 100;
        $totalFee = $taxableAmount + $taxAmount + $securityFee;

        // Pending and paid status helpers
        $latestPendingInvoice = $invoices->firstWhere('status', 'unpaid') ?? $invoices->firstWhere('status', 'pending');
        $hasPendingFee = (bool) $latestPendingInvoice || ($invoices->isEmpty() && $totalFee > 0);
        $dueDate = $latestPendingInvoice?->due_date ? \Carbon\Carbon::parse($latestPendingInvoice->due_date) : now()->addDays(7);
        $daysRemaining = (int) now()->diffInDays($dueDate, false);
        $paidCount = $invoices->where('status', 'paid')->count();
        $totalInvoicesCount = $invoices->count();

        // Institute Payment & Bank Settings (Configurable by Principal)
        $instituteId = $student->institute_id ?? auth()->user()?->institute_id ?? 1;
        $instituteSetting = InstituteSetting::getForInstitute($instituteId);

        $showPaymentDetails = (bool) ($instituteSetting->show_payment_details ?? true);
        $bankName = $instituteSetting->bank_name ?? 'Habib Bank Limited (HBL)';
        $bankAccountTitle = $instituteSetting->bank_account_title ?? 'Apex Educational Institute';
        $bankIban = $instituteSetting->bank_iban ?? 'PK75 HABB 0001 2345 6789 0123';
        $onebillPrefix = $instituteSetting->onebill_voucher_prefix ?? '100';
        $paymentInstructions = $instituteSetting->payment_instructions ?? 'Fees can be deposited at any partner bank branch nationwide or paid online via Mobile Banking Apps, ATM, Easypaisa, or JazzCash using your 1Bill Consumer Number.';

        return view('student.invoices', compact(
            'student',
            'invoices',
            'baseFee',
            'admissionFee',
            'securityFee',
            'scholarshipPercentage',
            'scholarshipName',
            'scholarshipDiscount',
            'netFeeAfterDiscount',
            'isFiler',
            'taxPercentage',
            'taxAmount',
            'totalFee',
            'latestPendingInvoice',
            'hasPendingFee',
            'dueDate',
            'daysRemaining',
            'paidCount',
            'totalInvoicesCount',
            'showPaymentDetails',
            'bankName',
            'bankAccountTitle',
            'bankIban',
            'onebillPrefix',
            'paymentInstructions'
        ));
    }

    /**
     * Enrolled Class Timetable
     * Route: /student/timetable
     */
    public function timetable()
    {
        $student = $this->getStudentForAuthUser();
        $classSection = $student?->classSection;
        $classSectionId = $student?->class_section_id;

        if (! $classSectionId) {
            $classSection = \App\Models\ClassSection::with('instituteClass')->first();
            $classSectionId = $classSection?->id;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timetablesByDay = collect();
        $weeklyGrid = [];
        $timeSlotsList = [];

        if ($classSectionId) {
            $rawSlots = \App\Models\Timetable::where('class_section_id', $classSectionId)
                ->with(['subject', 'teacher', 'room', 'section.instituteClass'])
                ->orderBy('start_time')
                ->get();

            $mergedSlots = \App\Models\Timetable::mergeContiguousSlots($rawSlots);
            $timetablesByDay = $mergedSlots->groupBy(fn ($item) => strtolower($item->day_of_week));

            // Extract unique sorted time slots for matrix view
            $timeSlotsList = $mergedSlots->map(function ($s) {
                return substr($s->start_time, 0, 5) . ' - ' . substr($s->end_time, 0, 5);
            })->unique()->values()->all();

            sort($timeSlotsList);

            foreach ($timeSlotsList as $tSlot) {
                foreach ($days as $day) {
                    $found = $mergedSlots->first(function ($s) use ($tSlot, $day) {
                        $range = substr($s->start_time, 0, 5) . ' - ' . substr($s->end_time, 0, 5);
                        return strtolower($s->day_of_week) === $day && $range === $tSlot;
                    });
                    $weeklyGrid[$tSlot][$day] = $found;
                }
            }
        }

        return view('student.timetable', compact('student', 'classSection', 'timetablesByDay', 'days', 'timeSlotsList', 'weeklyGrid'));
    }

    /**
     * Download Enrolled Class Timetable (.xlsx)
     * Strictly restricted to the authenticated student's enrolled class section.
     * Route: /student/timetable/download
     */
    public function downloadTimetable()
    {
        $student = $this->getStudentForAuthUser();

        if (! $student || ! $student->class_section_id) {
            return back()->with('error', 'No enrolled class found for your profile. Please contact administration.');
        }

        $classSection = $student->classSection;
        if (! $classSection) {
            $classSection = \App\Models\ClassSection::with('instituteClass')->find($student->class_section_id);
        }

        if (! $classSection) {
            return back()->with('error', 'Enrolled class details could not be found.');
        }

        $instituteId = $student->institute_id ?? auth()->user()->institute_id;
        $academicTermId = $student->academic_term_id;
        if (! $academicTermId) {
            $activeTerm = \App\Models\AcademicTerm::where('institute_id', $instituteId)->where('is_active', true)->first();
            $academicTermId = $activeTerm?->id;
        }

        if (! $academicTermId) {
            return back()->with('error', 'No active academic session found for timetable export.');
        }

        try {
            $excelService = new \App\Services\TimetableMultiSheetExcelService();
            $filePath = $excelService->generateClassTimetable($instituteId, $academicTermId, $student->class_section_id);

            $className = $classSection->instituteClass?->custom_name ?: ($classSection->instituteClass?->class_name ?: 'Class');
            $secName = $classSection->section_name ?: 'A';
            $sanitized = preg_replace('/[^A-Za-z0-9_]/', '_', "{$className}_{$secName}");
            $filename = "{$sanitized}_Timetable.xlsx";

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to generate Timetable Excel: ' . $e->getMessage());
        }
    }

    /**
     * My Enrolled Subjects
     * Route: /student/courses
     */
    public function courses()
    {
        $student = $this->getStudentForAuthUser();
        $classSection = $student?->classSection;
        $subjects = collect();

        if ($student && $student->class_section_id) {
            $classId = $classSection?->institute_class_id;
            $sectionId = $student->class_section_id;

            $subjects = \App\Models\Subject::where(function ($q) use ($classId, $sectionId) {
                if ($classId) {
                    $q->where('institute_class_id', $classId);
                }
                $q->orWhereHas('teacherAssignments', fn ($ta) => $ta->where('class_section_id', $sectionId));
            })
            ->with(['teacherAssignments' => fn ($ta) => $ta->where('class_section_id', $sectionId)->with('teacher'), 'materials', 'instituteClass', 'room'])
            ->withCount(['materials' => fn ($q) => $q->where('is_rag_indexed', true)])
            ->orderBy('subject_name')
            ->get();
        }

        return view('student.courses', compact('student', 'classSection', 'subjects'));
    }

    /**
     * Assignments & LMS Portal
     * Route: /student/lms
     */
    public function lms()
    {
        $student = $this->getStudentForAuthUser();
        $classSection = $student?->classSection;
        $subjects = collect();
        $assessments = collect();

        if ($student && $student->class_section_id) {
            $classId = $classSection?->institute_class_id;
            $sectionId = $student->class_section_id;

            $subjects = \App\Models\Subject::where(function ($q) use ($classId, $sectionId) {
                if ($classId) {
                    $q->where('institute_class_id', $classId);
                }
                $q->orWhereHas('teacherAssignments', fn ($ta) => $ta->where('class_section_id', $sectionId));
            })->get();

            $studentUserId = auth()->id();

            $assessments = \App\Models\Assessment::where(function ($q) use ($sectionId, $classId) {
                    $q->where('class_section_id', $sectionId);
                    if ($classId) {
                        $q->orWhereHas('classSection', fn ($cs) => $cs->where('institute_class_id', $classId));
                    }
                    $q->orWhereJsonContains('target_section_ids', (string) $sectionId)
                      ->orWhereJsonContains('target_section_ids', (int) $sectionId);
                })
                ->where('is_published_student', true)
                ->whereIn('status', [
                    \App\Models\Assessment::STATUS_PUBLISHED,
                    \App\Models\Assessment::STATUS_IN_PROGRESS,
                    \App\Models\Assessment::STATUS_GRADED
                ])
                ->with(['subject', 'classSection.instituteClass', 'questions'])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($asm) use ($studentUserId) {
                    $questionIds = $asm->questions->pluck('id');
                    $userAnswers = \App\Models\StudentAssessmentAnswer::whereIn('assessment_question_id', $questionIds)
                        ->where('student_id', $studentUserId)
                        ->get();

                    $asm->has_submitted = $userAnswers->isNotEmpty();
                    $asm->total_score_obtained = $userAnswers->sum('marks_awarded');
                    return $asm;
                });
        }

        return view('student.lms', compact('student', 'classSection', 'subjects', 'assessments'));
    }

    /**
     * Official Class Exam Datesheet
     * Route: /student/datesheet
     */
    public function datesheet()
    {
        $student = $this->getStudentForAuthUser();
        $classSection = $student?->classSection;
        $examSchedules = collect();

        if ($student && $student->class_section_id) {
            $sectionId = $student->class_section_id;
            $classId = $classSection?->institute_class_id;

            $examSchedules = \App\Models\Assessment::where(function ($q) use ($sectionId, $classId) {
                    $q->where('class_section_id', $sectionId);
                    if ($classId) {
                        $q->orWhereHas('classSection', fn ($cs) => $cs->where('institute_class_id', $classId));
                    }
                    $q->orWhereJsonContains('target_section_ids', (string) $sectionId)
                      ->orWhereJsonContains('target_section_ids', (int) $sectionId);
                })
                ->where('is_published_student', true)
                ->whereIn('type', [\App\Models\Assessment::TYPE_MIDTERM, \App\Models\Assessment::TYPE_FINAL, 'exam', 'quiz'])
                ->whereIn('status', [
                    \App\Models\Assessment::STATUS_PUBLISHED,
                    \App\Models\Assessment::STATUS_IN_PROGRESS,
                    \App\Models\Assessment::STATUS_GRADED
                ])
                ->with(['subject', 'classSection.instituteClass', 'academicTerm', 'creator:id,name'])
                ->orderBy('start_time', 'asc')
                ->get();
        }

        return view('student.datesheet', compact('student', 'classSection', 'examSchedules'));
    }

    /**
     * Official Midterm & Final Term Exam Report
     * Route: /student/exam-report
     */
    public function examReport()
    {
        $student = $this->getStudentForAuthUser();
        $classSection = $student?->classSection;
        $examReports = collect();

        if ($student && $student->class_section_id) {
            $sectionId = $student->class_section_id;
            $classId = $classSection?->institute_class_id;

            $examReports = \App\Models\Assessment::where(function ($q) use ($sectionId, $classId) {
                    $q->where('class_section_id', $sectionId);
                    if ($classId) {
                        $q->orWhereHas('classSection', fn ($cs) => $cs->where('institute_class_id', $classId));
                    }
                })
                ->where('is_published_student', true)
                ->whereIn('type', [\App\Models\Assessment::TYPE_MIDTERM, \App\Models\Assessment::TYPE_FINAL, 'exam'])
                ->with(['subject', 'classSection.instituteClass', 'academicTerm', 'questions'])
                ->orderBy('start_time', 'asc')
                ->get()
                ->map(function ($exam) use ($student) {
                    $qIds = $exam->questions->pluck('id');
                    $answers = collect();
                    if ($qIds->isNotEmpty() && $student->user_id) {
                        $answers = \App\Models\StudentAssessmentAnswer::whereIn('assessment_question_id', $qIds)
                            ->where('student_id', $student->user_id)
                            ->get()
                            ->keyBy('assessment_question_id');
                    }

                    $questionBreakdown = [];
                    $totalObtained = 0;
                    $hasAnyMarks = false;

                    foreach ($exam->questions as $q) {
                        $ans = $answers->get($q->id);
                        $obtained = $ans ? $ans->marks_awarded : null;
                        if ($obtained !== null) {
                            $hasAnyMarks = true;
                            $totalObtained += (float) $obtained;
                        }
                        $questionBreakdown[] = [
                            'question_id' => $q->id,
                            'statement' => $q->statement ?: ('Q'.$q->sort_order),
                            'max_marks' => $q->marks,
                            'obtained_marks' => $obtained,
                        ];
                    }

                    $exam->question_breakdown = $questionBreakdown;
                    $exam->obtained_marks = $hasAnyMarks ? $totalObtained : null;
                    $exam->is_graded = $hasAnyMarks;
                    return $exam;
                });
        }

        return view('student.exam_report', compact('student', 'classSection', 'examReports'));
    }
}
