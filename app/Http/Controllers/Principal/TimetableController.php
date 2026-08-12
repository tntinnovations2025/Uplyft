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

        $sections = ClassSection::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))
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
        if (!$selectedSectionId && $activeTerm) {
            $firstSectionWithSlots = Timetable::where('academic_term_id', $activeTerm->id)->value('class_section_id');
            $selectedSectionId = $firstSectionWithSlots ?: $sections->first()?->id;
        } elseif (!$selectedSectionId) {
            $selectedSectionId = $sections->first()?->id;
        }
        $selectedSection = $sections->firstWhere('id', $selectedSectionId);

        // Fetch course assignments (teacher → subject → section) for auto generator
        $assignments = collect();
        if ($activeTerm) {
            $assignments = TeacherSubjectSection::where('academic_term_id', $activeTerm->id)
                ->with(['section.instituteClass', 'subject', 'teacher'])
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

        // Build unique time slots
        $timeSlots = $allSlots->map(fn($s) => [
            'start' => substr($s->start_time, 0, 5),
            'end'   => substr($s->end_time, 0, 5),
            'key'   => substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5),
        ])->unique('key')->sortBy('start')->values();

        if ($timeSlots->isEmpty()) {
            $defaultSlots = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00'];
            foreach ($defaultSlots as $ds) {
                [$s, $e] = explode('-', $ds);
                $timeSlots->push(['start' => $s, 'end' => $e, 'key' => $ds]);
            }
        }

        // Grid data: gridData[section_id][day][timeKey] = slot
        $gridData = [];
        foreach ($allSlots as $slot) {
            $secId   = $slot->class_section_id;
            $day     = strtolower($slot->day_of_week);
            $timeKey = substr($slot->start_time, 0, 5) . '-' . substr($slot->end_time, 0, 5);
            $gridData[$secId][$day][$timeKey] = $slot;
        }

        // Group sections by Class
        $groupedSections = $sections->groupBy(fn($s) => $s->instituteClass->custom_name ?? 'Other');

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
            'timetables',
            'days',
            'allSlots',
            'timeSlots',
            'gridData',
            'groupedSections'
        ));
    }

    public function generate(Request $request): RedirectResponse
    {
        $instituteId = auth()->user()->institute_id;
        $activeTerm  = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (!$activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        $result = $this->generatorService->generate(
            $activeTerm->id,
            $instituteId
        );

        if (!$result['success']) {
            return back()->with('error', implode('<br>', $result['clashes']));
        }

        $clashMsg = '';
        if (!empty($result['clashes'])) {
            $clashMsg = "⚡ Generated {$result['scheduled_slots']} slots with clashes:<br>" . implode('<br>', $result['clashes']);
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

        $sections = ClassSection::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))
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

        // Build unique time slots from all timetable entries
        $timeSlots = $allSlots->map(fn($s) => [
            'start' => substr($s->start_time, 0, 5),
            'end'   => substr($s->end_time, 0, 5),
            'key'   => substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5),
        ])->unique('key')->sortBy('start')->values();

        // Build grid data: grid[section_id][day][timeKey] = slot info
        $grid = [];
        foreach ($allSlots as $slot) {
            $secId   = $slot->class_section_id;
            $day     = $slot->day_of_week;
            $timeKey = substr($slot->start_time, 0, 5) . '-' . substr($slot->end_time, 0, 5);

            $grid[$secId][$day][$timeKey] = $slot;
        }

        // Group sections that have slots
        $activeSections = $sections->filter(fn($s) => isset($grid[$s->id]));
        if ($selectedSectionId) {
            $activeSections = $sections->filter(fn($s) => $s->id == $selectedSectionId);
        }

        $selectedDay = $request->get('day', 'monday');

        return view('principal.timetables.grid', compact(
            'activeTerm',
            'sections',
            'selectedSectionId',
            'selectedDay',
            'activeSections',
            'days',
            'timeSlots',
            'grid'
        ));
    }

    public function exportExcel(Request $request)
    {
        $instituteId = auth()->user()->institute_id;

        $activeTerm = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (!$activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        $sections = ClassSection::whereHas('instituteClass', fn($q) => $q->where('institute_id', $instituteId))
            ->with('instituteClass')
            ->get();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        $allSlots = Timetable::where('academic_term_id', $activeTerm->id)
            ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
            ->orderBy('start_time')
            ->get();

        // Build unique time slots
        $timeSlots = $allSlots->map(fn($s) => [
            'start' => substr($s->start_time, 0, 5),
            'end'   => substr($s->end_time, 0, 5),
            'key'   => substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5),
        ])->unique('key')->sortBy('start')->values();

        // Default time slots if empty
        if ($timeSlots->isEmpty()) {
            $defaultSlots = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00'];
            foreach ($defaultSlots as $ds) {
                [$s, $e] = explode('-', $ds);
                $timeSlots->push(['start' => $s, 'end' => $e, 'key' => $ds]);
            }
        }

        // Build grid[section_id][day][timeKey]
        $grid = [];
        foreach ($allSlots as $slot) {
            $secId   = $slot->class_section_id;
            $day     = strtolower($slot->day_of_week);
            $timeKey = substr($slot->start_time, 0, 5) . '-' . substr($slot->end_time, 0, 5);
            $grid[$secId][$day][$timeKey] = $slot;
        }

        // Group sections by Class (e.g. Grade 9, Grade 10...)
        $groupedSections = $sections->groupBy(fn($s) => $s->instituteClass->custom_name ?? 'Other');

        // Build CSV content
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM for proper Excel Unicode
        $csv .= "UPLYFT Master School Timetable - " . ($activeTerm->name ?? 'Session') . "\n\n";

        foreach ($groupedSections as $className => $classSections) {
            $csv .= "\"==================================================\"\n";
            $csv .= "\"CLASS: {$className}\"\n";
            $csv .= "\"==================================================\"\n\n";

            foreach ($classSections as $section) {
                $sectionLabel = ($section->instituteClass->custom_name ?? '') . ' - ' . ($section->section_name ?? '');
                $csv .= "\"SECTION TABLE: {$sectionLabel}\"\n";

                // Header row: Time Slot | Monday | Tuesday | Wednesday | Thursday | Friday | Saturday
                $csv .= "\"Time Slot\"";
                foreach ($days as $day) {
                    $csv .= ",\"" . ucfirst($day) . "\"";
                }
                $csv .= "\n";

                // Rows: Time slots
                foreach ($timeSlots as $ts) {
                    $csv .= "\"{$ts['start']} - {$ts['end']}\"";
                    foreach ($days as $day) {
                        $slot = $grid[$section->id][$day][$ts['key']] ?? null;
                        if ($slot) {
                            $roomStr = $slot->room ? ' Room: ' . $slot->room->room_number : ' Room: Unassigned';
                            $cell = ($slot->subject->subject_name ?? '') . ' | ' .
                                    ($slot->teacher->name ?? '') . ' |' .
                                    $roomStr;
                            $csv .= ",\"{$cell}\"";
                        } else {
                            $csv .= ",\"-\"";
                        }
                    }
                    $csv .= "\n";
                }
                $csv .= "\n\n";
            }
        }

        $filename = 'UPLYFT_Master_Timetable_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id'       => 'required|exists:subjects,id',
            'teacher_id'       => 'required|exists:users,id',
            'room_id'          => 'nullable|exists:rooms,id',
            'day_of_week'      => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
        ]);

        $instituteId = auth()->user()->institute_id;
        $activeTerm  = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        if (!$activeTerm) {
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
            isset($validated['room_id']) ? (int) $validated['room_id'] : null
        );

        if ($validation['has_conflict']) {
            return back()->withInput()->with('error', $validation['message']);
        }

        Timetable::create([
            'academic_term_id' => $activeTerm->id,
            'class_section_id' => $validated['class_section_id'],
            'subject_id'       => $validated['subject_id'],
            'teacher_id'       => $validated['teacher_id'],
            'room_id'          => $validated['room_id'] ?? null,
            'day_of_week'      => strtolower($validated['day_of_week']),
            'start_time'       => $validated['start_time'],
            'end_time'         => $validated['end_time'],
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
}
