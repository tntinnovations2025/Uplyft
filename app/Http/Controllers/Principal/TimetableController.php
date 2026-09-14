<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Room;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use App\Models\Timetable;
use App\Models\User;
use App\Services\TimetableChatService;
use App\Services\TimetableConflictService;
use App\Services\TimetableGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function __construct(
        protected TimetableConflictService $conflictService,
        protected TimetableGeneratorService $generatorService,
        protected TimetableChatService $chatService
    ) {}

    public function index(Request $request): View
    {
        $instituteId = auth()->user()->institute_id;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        $sections = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId, $activeTerm) {
            $q->where('institute_id', $instituteId);
            if ($activeTerm) {
                $q->where('academic_term_id', $activeTerm->id);
            }
        })
            ->with('instituteClass')
            ->get();

        $teachers = User::where('institute_id', $instituteId)
            ->where('role', User::ROLE_TEACHER)
            ->with('availabilities')
            ->orderBy('name')
            ->get();

        $rooms = Room::where('institute_id', $instituteId)->orderBy('room_number')->get();

        // Intelligently select default section: use request section_id, or first section with scheduled slots, or first section
        $selectedSectionId = $request->get('section_id');
        if (! $selectedSectionId && $activeTerm) {
            $firstSectionWithSlots = Timetable::where('academic_term_id', $activeTerm->id)->value('class_section_id');
            $selectedSectionId = $firstSectionWithSlots ?: $sections->first()?->id;
        } elseif (! $selectedSectionId) {
            $selectedSectionId = $sections->first()?->id;
        }
        $selectedSection = $sections->firstWhere('id', $selectedSectionId);

        // Fetch course assignments (teacher → subject → section) for auto generator
        $assignments = collect();
        if ($activeTerm) {
            $assignments = TeacherSubjectSection::where('academic_term_id', $activeTerm->id)
                ->with(['section.instituteClass', 'subject', 'teacher.availabilities'])
                ->orderBy('teacher_id')
                ->get();
        }

        // Fetch subjects associated with selected section's class for manual slot addition
        $subjects = collect();
        if ($selectedSection) {
            $subjects = Subject::where('institute_class_id', $selectedSection->institute_class_id)
                ->with('instituteClass')
                ->get();
        }

        $allSlots = collect();
        if ($activeTerm) {
            $allSlots = Timetable::where('academic_term_id', $activeTerm->id)
                ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
                ->orderBy('start_time')
                ->get();
        }

        // Standard 1-hour time slots matrix header columns (08:00 - 15:00)
        $timeSlots = collect();
        $defaultSlots = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00'];
        foreach ($defaultSlots as $ds) {
            [$s, $e] = explode('-', $ds);
            $timeSlots->push(['start' => $s, 'end' => $e, 'key' => $ds]);
        }

        // Grid data: gridData[section_id][day][timeKey] = slot
        $gridData = [];
        $roomGridData = [];
        $roomTimeGrid = [];
        foreach ($allSlots as $slot) {
            $secId = $slot->class_section_id;
            $day = strtolower($slot->day_of_week);
            $timeKey = substr($slot->start_time, 0, 5).'-'.substr($slot->end_time, 0, 5);
            $gridData[$secId][$day][$timeKey] = $slot;

            $roomId = $slot->room_id ?: 0;
            $roomGridData[$secId][$day][$roomId][] = $slot;
            $roomTimeGrid[$roomId][$day][$timeKey][] = $slot;
        }

        // Teacher Personal & View Modes: 'teacher', 'class', 'time'
        $user = auth()->user();
        $viewType = $request->get('view_type');
        $selectedTeacherId = $request->get('teacher_id');
        $selectedTimeSlot = $request->get('time_slot');

        // Normalize view types: 'teacher', 'class'
        if (!$viewType || $viewType === 'select') {
            if ($user->isTeacher() && !$user->isPrincipal() && !$request->has('view_type') && !$request->has('section_id')) {
                $viewType = 'teacher';
                if (!$selectedTeacherId) {
                    $selectedTeacherId = $user->id;
                }
            } else {
                $viewType = 'class';
            }
        } elseif ($viewType === 'full' || $viewType === 'section') {
            $viewType = 'class';
        } elseif ($viewType === 'my') {
            $viewType = 'teacher';
            if (!$selectedTeacherId) {
                $selectedTeacherId = $user->id;
            }
        }

        // Selected Teacher Model
        $selectedTeacher = $selectedTeacherId ? $teachers->firstWhere('id', $selectedTeacherId) : null;

        // Filtered slots for Teacher view
        $teacherSlots = $allSlots;
        if ($selectedTeacherId) {
            $teacherSlots = $allSlots->filter(fn($s) => $s->teacher_id == $selectedTeacherId);
        }
        $teacherSlotsByTeacher = $teacherSlots->groupBy('teacher_id');

        // Teacher Personal Slots (for legacy compat)
        $mySlots = $allSlots->filter(fn ($s) => $s->teacher_id == $user->id);
        $myGridData = [];
        foreach ($mySlots as $slot) {
            $day = strtolower($slot->day_of_week);
            $timeKey = substr($slot->start_time, 0, 5).'-'.substr($slot->end_time, 0, 5);
            $myGridData[$day][$timeKey] = $slot;
        }

        // Filtered slots for Time view
        $timeFilteredSlots = $allSlots;
        if ($selectedTimeSlot) {
            $timeFilteredSlots = $allSlots->filter(function($s) use ($selectedTimeSlot) {
                $k = substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5);
                return $k === $selectedTimeSlot || str_contains($k, $selectedTimeSlot);
            });
        }

        // Group sections by Class
        $groupedSections = $sections->groupBy(fn ($s) => $s->instituteClass->custom_name ?? 'Other');

        $timetables = collect();
        if ($activeTerm && $selectedSectionId) {
            $timetables = Timetable::where('academic_term_id', $activeTerm->id)
                ->where('class_section_id', $selectedSectionId)
                ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        return view('principal.timetables.index', compact(
            'activeTerm',
            'sections',
            'selectedSection',
            'teachers',
            'subjects',
            'rooms',
            'assignments',
            'selectedSectionId',
            'selectedTeacherId',
            'selectedTeacher',
            'selectedTimeSlot',
            'teacherSlots',
            'teacherSlotsByTeacher',
            'timeFilteredSlots',
            'timetables',
            'days',
            'allSlots',
            'timeSlots',
            'gridData',
            'roomGridData',
            'roomTimeGrid',
            'groupedSections',
            'viewType',
            'mySlots',
            'myGridData'
        ));
    }

    public function generate(Request $request): RedirectResponse
    {
        $instituteId = auth()->user()->institute_id;
        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (! $activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        $result = $this->generatorService->generate(
            $activeTerm->id,
            $instituteId
        );

        if (! $result['success']) {
            return back()->with('error', implode('<br>', $result['clashes']));
        }

        $clashMsg = '';
        if (! empty($result['clashes'])) {
            $clashMsg = "⚡ Generated {$result['scheduled_slots']} slots with clashes:<br>".implode('<br>', $result['clashes']);
        }

        return back()
            ->with('success', empty($clashMsg) ? "🎉 Optimistic Timetable generated successfully with {$result['scheduled_slots']} slots and 0 clashes!" : null)
            ->with('warning', $clashMsg ?: null);
    }

    public function generatedGrid(Request $request): View
    {
        $instituteId = auth()->user()->institute_id;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        $sections = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId, $activeTerm) {
            $q->where('institute_id', $instituteId);
            if ($activeTerm) {
                $q->where('academic_term_id', $activeTerm->id);
            }
        })
            ->with('instituteClass')
            ->get();

        $selectedSectionId = $request->get('section_id');

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        // Collect all timetable slots for the term
        $allSlots = collect();
        if ($activeTerm) {
            $query = Timetable::where('academic_term_id', $activeTerm->id)
                ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
                ->orderBy('start_time');

            if ($selectedSectionId) {
                $query->where('class_section_id', $selectedSectionId);
            }

            $allSlots = $query->get();
        }

        // Dynamically compute unique time slots from actual scheduled timetable slots
        $timeSlots = $allSlots->map(function ($s) {
            $st = substr($s->start_time, 0, 5);
            $et = substr($s->end_time, 0, 5);
            return [
                'start' => $st,
                'end'   => $et,
                'key'   => "{$st}-{$et}",
                'sort'  => $st . '_' . $et,
            ];
        })->unique('key')->sortBy('sort')->values();

        if ($timeSlots->isEmpty()) {
            $defaultSlots = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00'];
            foreach ($defaultSlots as $ds) {
                [$s, $e] = explode('-', $ds);
                $timeSlots->push(['start' => $s, 'end' => $e, 'key' => $ds, 'sort' => $s . '_' . $e]);
            }
        }

        $rooms = Room::where('institute_id', $instituteId)->orderBy('room_number')->get();

        // Build Excel-sheet style grid matrix: excelGrid[day][room_id][timeKey] = slot
        // Also build standard grid[section_id][day][timeKey] = slot
        $grid = [];
        $roomGrid = [];
        $roomTimeGrid = [];
        $excelGrid = [];

        foreach ($allSlots as $slot) {
            $secId = $slot->class_section_id;
            $day = strtolower($slot->day_of_week);
            $st = substr($slot->start_time, 0, 5);
            $et = substr($slot->end_time, 0, 5);
            $timeKey = "{$st}-{$et}";

            $grid[$secId][$day][$timeKey] = $slot;

            $roomId = $slot->room_id ?: 0;
            $roomGrid[$secId][$day][$roomId][] = $slot;
            $roomTimeGrid[$roomId][$day][$timeKey][] = $slot;

            // Map slot to exact timeKey or overlapping timeSlots
            $excelGrid[$day][$roomId][$timeKey] = $slot;
        }

        // Group sections that have slots
        $activeSections = $sections->filter(fn ($s) => isset($grid[$s->id]));
        if ($selectedSectionId) {
            $activeSections = $sections->filter(fn ($s) => $s->id == $selectedSectionId);
        }

        $selectedDay = strtolower($request->get('day', 'all'));

        return view('principal.timetables.grid', compact(
            'activeTerm',
            'sections',
            'selectedSectionId',
            'selectedDay',
            'activeSections',
            'days',
            'timeSlots',
            'grid',
            'rooms',
            'roomGrid',
            'roomTimeGrid',
            'excelGrid',
            'allSlots'
        ));
    }

    public function exportExcel(Request $request)
    {
        $instituteId = auth()->user()->institute_id;
        $institute = auth()->user()->institute ?? \App\Models\Institute::find($instituteId);

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (! $activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        $classSectionId = $request->get('class_section_id');

        try {
            $excelService = new \App\Services\TimetableMultiSheetExcelService();

            if ($classSectionId) {
                $section = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })->with('instituteClass')->findOrFail($classSectionId);

                $filePath = $excelService->generateClassTimetable($instituteId, $activeTerm->id, $section->id);

                $className = $section->instituteClass?->custom_name ?: ($section->instituteClass?->class_name ?: 'Class');
                $secName = $section->section_name ?: 'A';
                $sanitized = preg_replace('/[^A-Za-z0-9_]/', '_', "{$className}_{$secName}");
                $filename = "{$sanitized}_Timetable.xlsx";
            } else {
                $filePath = $excelService->generateCompleteInstitute($instituteId, $activeTerm->id);
                $filename = "Institute_Complete_Timetable.xlsx";
            }

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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $instituteId = auth()->user()->institute_id;

        // Enforce tenant boundaries on every foreign key before creating a slot.
        $section = \App\Models\ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->find($validated['class_section_id']);
        $subject = \App\Models\Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->find($validated['subject_id']);
        $teacher = \App\Models\User::where('institute_id', $instituteId)->find($validated['teacher_id']);
        if (! $section || ! $subject || ! $teacher) {
            return back()->withInput()->with('error', 'Chosen section, subject, or teacher does not belong to your institute.');
        }
        if (! empty($validated['room_id'])) {
            $room = \App\Models\Room::where('institute_id', $instituteId)->find($validated['room_id']);
            if (! $room) {
                return back()->withInput()->with('error', 'Chosen room does not belong to your institute.');
            }
        }

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (! $activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        // Run Conflict Check via TimetableConflictService
        $validation = $this->conflictService->validateConflict(
            $activeTerm->id,
            (int) $validated['class_section_id'],
            (int) $validated['teacher_id'],
            $validated['day_of_week'],
            $validated['start_time'],
            $validated['end_time'],
            isset($validated['room_id']) ? (int) $validated['room_id'] : null,
            null,
            (int) $validated['subject_id']
        );

        if ($validation['has_conflict']) {
            return back()->withInput()->with('error', $validation['message']);
        }

        Timetable::create([
            'academic_term_id' => $activeTerm->id,
            'class_section_id' => $validated['class_section_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'room_id' => $validated['room_id'] ?? null,
            'day_of_week' => strtolower($validated['day_of_week']),
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return redirect()
            ->route('principal.timetables.index', ['section_id' => $validated['class_section_id']])
            ->with('success', 'Timetable slot scheduled successfully with 0 conflicts!');
    }

    public function destroy(Timetable $timetable): RedirectResponse
    {
        if ($timetable->academicTerm->institute_id !== auth()->user()->institute_id) {
            abort(403);
        }

        $sectionId = $timetable->class_section_id;
        $timetable->delete();

        return redirect()
            ->route('principal.timetables.index', ['section_id' => $sectionId])
            ->with('success', 'Timetable slot removed.');
    }

    /**
     * Update Subject Allocation Class Duration, Periods per Week & Allowed Days
     */
    public function updateAllocationDuration(Request $request, TeacherSubjectSection $allocation): RedirectResponse|JsonResponse
    {
        $instituteId = auth()->user()->institute_id;

        // Re-resolve the allocation through the tenant path; do not trust the route binding.
        $allocation = TeacherSubjectSection::where('id', $allocation->id)
            ->whereHas('subject.instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->first();

        if (! $allocation) {
            abort(403, 'Allocation does not belong to your institute.');
        }

        $validated = $request->validate([
            'hours' => ['required', 'integer', 'min:0', 'max:8'],
            'minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'periods_per_week' => ['nullable', 'integer', 'min:1', 'max:20'],
            'allowed_days' => ['nullable', 'array'],
            'allowed_days.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
        ]);

        $hours = (int) $validated['hours'];
        $minutes = (int) $validated['minutes'];
        $totalMinutes = ($hours * 60) + $minutes;

        if ($totalMinutes < 15) {
            $totalMinutes = 15; // Minimum 15 minutes class duration
        }

        $allowedDays = !empty($validated['allowed_days'])
            ? array_map('strtolower', $validated['allowed_days'])
            : ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        $allocation->update([
            'duration_minutes' => $totalMinutes,
            'periods_per_week' => $validated['periods_per_week'] ?? $allocation->periods_per_week,
            'allowed_days' => $allowedDays,
        ]);

        // Auto-regenerate timetable using updated allocation settings
        $result = $this->generatorService->regenerateForActiveTerm(auth()->user()->institute_id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Allocation settings updated & timetable re-generated.',
                'formatted_duration' => $allocation->formatted_duration,
                'duration_minutes' => $allocation->duration_minutes,
                'allowed_days' => $allocation->formatted_allowed_days,
            ]);
        }

        $subjectName = $allocation->subject->subject_name ?? 'Subject';
        $className = $allocation->section->instituteClass->custom_name ?? 'Class';

        $msg = "⏱️ Settings for {$subjectName} ({$className}) updated to {$hours}h {$minutes}m ({$allocation->periods_per_week} periods/week, Days: {$allocation->formatted_allowed_days}). Timetable re-generated automatically.";
        if ($result && !empty($result['clashes'])) {
            return redirect()->back()->with('success', $msg)->with('warning', implode('<br>', $result['clashes']));
        }

        return redirect()->back()->with('success', $msg);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $instituteId = auth()->user()->institute_id;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        $result = $this->chatService->handle(
            $validated['message'],
            $instituteId,
            $activeTerm
        );

        return response()->json($result);
    }

    /**
     * Display dedicated Class Days & Work Hours Management page
     */
    public function daysAndHours(Request $request)
    {
        $instituteId = auth()->user()->institute_id;
        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        $assignments = collect();
        if ($activeTerm) {
            $assignments = TeacherSubjectSection::where('academic_term_id', $activeTerm->id)
                ->with(['section.instituteClass', 'subject', 'teacher.availabilities'])
                ->orderBy('teacher_id')
                ->get();
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        return view('principal.timetables.days_and_hours', compact('activeTerm', 'assignments', 'days'));
    }
}
