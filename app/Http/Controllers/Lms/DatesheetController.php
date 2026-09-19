<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Assessment;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DatesheetController extends Controller
{
    /**
     * Display the Official Exam Datesheet Directory
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isStudent()) {
            return redirect()->route('student.datesheet');
        }

        $instituteId = $user->institute_id;

        // Fetch academic terms with robust fallbacks
        $academicTerms = AcademicTerm::all();
        if ($academicTerms->isEmpty()) {
            $academicTerms = collect([
                AcademicTerm::firstOrCreate(
                    ['term_name' => 'Midterm Fall 2026'],
                    ['institute_id' => $instituteId ?? 1, 'start_date' => now(), 'end_date' => now()->addMonths(3), 'is_active' => true]
                ),
                AcademicTerm::firstOrCreate(
                    ['term_name' => 'Final Term 2026'],
                    ['institute_id' => $instituteId ?? 1, 'start_date' => now()->addMonths(4), 'end_date' => now()->addMonths(7), 'is_active' => false]
                ),
            ]);
        }

        $classSections = ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->with(['instituteClass', 'subjects'])
            ->get()
            ->sortBy(fn ($cs) => ($cs->instituteClass?->custom_name ?? '').' '.$cs->section_name);

        $classIds = $classSections->pluck('institute_class_id')->filter()->unique();
        $allClassSubjects = Subject::whereIn('institute_class_id', $classIds)->get()->groupBy('institute_class_id');

        // Fallback: If section->subjects relation is empty, load batch-fetched subjects by institute_class_id
        foreach ($classSections as $cs) {
            if ($cs->subjects->isEmpty() && $cs->institute_class_id) {
                $cs->setRelation('subjects', $allClassSubjects->get($cs->institute_class_id, collect()));
            }
        }

        $subjects = Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->orderBy('subject_name')
            ->get()
            ->unique('subject_name');

        if ($subjects->isEmpty()) {
            $subjects = Subject::all()->unique('subject_name');
        }

        // Query Exam Schedules
        $query = Assessment::whereHas('subject.instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->with(['subject', 'classSection.instituteClass', 'academicTerm', 'creator:id,name'])
            ->orderBy('start_time', 'asc');

        if ($user->isTeacher()) {
            $query->where('is_published_teacher', true);
            $teacher = $user->teacherProfile;
            $assignedSectionIds = [];
            if ($teacher) {
                $assignedSectionIds = TeacherSubjectSection::where('teacher_id', $teacher->id)
                    ->pluck('class_section_id')
                    ->unique()
                    ->toArray();
            }
            if (! empty($assignedSectionIds)) {
                $query->whereIn('class_section_id', $assignedSectionIds);
            }
        } elseif ($user->isStudent()) {
            $query->where('is_published_student', true);
        }

        if ($request->filled('class_section_id')) {
            $query->where('class_section_id', $request->class_section_id);
        }

        if ($request->filled('academic_term_id')) {
            $query->where('academic_term_id', $request->academic_term_id);
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('subject', function ($q) use ($request) {
                $q->where('subject_name', $request->subject_id)
                    ->orWhere('id', $request->subject_id);
            });
        }

        $datesheetEntries = $query->get();
        if ($datesheetEntries->isEmpty() && ! $request->hasAny(['class_section_id', 'academic_term_id', 'subject_id'])) {
            $fallbackQuery = Assessment::whereIn('type', ['midterm', 'final', 'exam'])
                ->with(['subject', 'classSection.instituteClass', 'academicTerm', 'creator:id,name'])
                ->orderBy('start_time', 'asc');

            if ($user->isTeacher()) {
                $fallbackQuery->where('is_published_teacher', true);
            } elseif ($user->isStudent()) {
                $fallbackQuery->where('is_published_student', true);
            }

            $datesheetEntries = $fallbackQuery->get();
        }

        // Group by Class Section name
        $groupedDatesheet = $datesheetEntries->groupBy(function ($item) {
            $cName = $item->classSection?->instituteClass?->custom_name ?? 'Class';
            $sName = $item->classSection?->section_name ?? '';
            return trim("{$cName} - {$sName}");
        });

        $registeredRooms = \App\Models\Room::where('institute_id', $instituteId)
            ->orderBy('room_number')
            ->get();

        if ($registeredRooms->isEmpty()) {
            $registeredRooms = \App\Models\Room::orderBy('room_number')->get();
        }

        return view('lms.datesheet.index', compact(
            'academicTerms',
            'classSections',
            'subjects',
            'registeredRooms',
            'datesheetEntries',
            'groupedDatesheet'
        ));
    }

    /**
     * Store new Exam Datesheet Entry (Principal / Admin Only)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (! $user->isAdministration()) {
            return redirect()->back()->with('error', 'Unauthorized: Only Principal or Administration can create Exam Datesheets.');
        }

        // Handle multi-class batch schedule payload
        if ($request->has('class_schedules') && is_array($request->class_schedules)) {
            $createdCount = 0;
            $title = $request->input('title', 'Midterm Examination');
            $typeInput = $request->input('type') ?: $title;
            $enumType = $this->normalizeEnumType($typeInput, $title);
            $termId = $request->input('academic_term_id');

            foreach ($request->class_schedules as $sectionId => $papers) {
                if (! is_array($papers)) continue;

                foreach ($papers as $paper) {
                    if (empty($paper['subject_id']) || empty($paper['exam_date'])) continue;

                    $subjectId = $paper['subject_id'];
                    $examDate = $paper['exam_date'];
                    $startTimeStr = $paper['start_time'] ?? '09:00';
                    $endTimeStr = $paper['end_time'] ?? '11:30';
                    $totalMarks = (int) ($paper['total_marks'] ?? 100);
                    $resDeadlineStr = $paper['result_deadline'] ?? null;

                    $startDateTime = Carbon::parse($examDate.' '.$startTimeStr);
                    $endDateTime = Carbon::parse($examDate.' '.$endTimeStr);

                    if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
                        $endDateTime->addDay();
                    }

                    $durationMinutes = $startDateTime->diffInMinutes($endDateTime);

                    $resultDeadlineTime = ! empty($resDeadlineStr)
                        ? Carbon::parse($resDeadlineStr)->endOfDay()
                        : $startDateTime->copy()->addDays(7);

                    Assessment::updateOrCreate(
                        [
                            'class_section_id' => $sectionId,
                            'subject_id' => $subjectId,
                            'title' => $title,
                        ],
                        [
                            'type' => $enumType,
                            'academic_term_id' => $termId,
                            'creator_id' => $user->id,
                            'start_time' => $startDateTime,
                            'end_time' => $endDateTime,
                            'result_deadline' => $resultDeadlineTime,
                            'has_time_limit' => true,
                            'duration_minutes' => $durationMinutes,
                            'total_marks' => $totalMarks,
                            'room' => $paper['room'] ?? $request->input('room', 'Unassigned'),
                            'is_published_teacher' => isset($request->input("class_visibility.{$sectionId}")['is_published_teacher'])
                                ? filter_var($request->input("class_visibility.{$sectionId}.is_published_teacher"), FILTER_VALIDATE_BOOLEAN)
                                : (isset($paper['is_published_teacher']) ? filter_var($paper['is_published_teacher'], FILTER_VALIDATE_BOOLEAN) : true),
                            'is_published_student' => isset($request->input("class_visibility.{$sectionId}")['is_published_student'])
                                ? filter_var($request->input("class_visibility.{$sectionId}.is_published_student"), FILTER_VALIDATE_BOOLEAN)
                                : (isset($paper['is_published_student']) ? filter_var($paper['is_published_student'], FILTER_VALIDATE_BOOLEAN) : true),
                            'instructions' => $paper['instructions'] ?? $request->input('instructions'),
                            'status' => Assessment::STATUS_PUBLISHED,
                            'is_marksheet_saved' => true,
                            'saved_at' => now(),
                        ]
                    );

                    $createdCount++;
                }
            }

            if ($createdCount > 0) {
                try {
                    $sections = \App\Models\ClassSection::whereIn('id', $sectionIds)->get();
                    foreach ($sections as $sec) {
                        \App\Services\PortalNotificationService::notifyStudentDatesheetPublished(
                            $instituteId,
                            $sec->id,
                            $title,
                            $sec->section_name
                        );
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Failed to dispatch datesheet student notification: " . $e->getMessage());
                }
            }

            return redirect()->back()->with('success', "Official Exam Datesheet published successfully for {$createdCount} scheduled exam paper(s)!");
        }

        // Single / Standard payload fallback
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
            'class_section_ids' => 'required|array|min:1',
            'class_section_ids.*' => 'exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'result_deadline' => 'nullable|date',
            'total_marks' => 'required|integer|min:1|max:1000',
            'room' => 'nullable|string|max:255',
            'is_published_teacher' => 'nullable|boolean',
            'is_published_student' => 'nullable|boolean',
            'instructions' => 'nullable|string|max:2000',
        ]);

        $activeTerm = AcademicTerm::where('is_active', true)->first() ?? AcademicTerm::first();
        $termId = $validated['academic_term_id'] ?? $activeTerm?->id;
        $type = $validated['type'] ?? $validated['title'];

        $startDateTime = Carbon::parse($validated['exam_date'].' '.$validated['start_time']);
        $endDateTime = Carbon::parse($validated['exam_date'].' '.$validated['end_time']);
        $resultDeadlineTime = ! empty($validated['result_deadline'])
            ? Carbon::parse($validated['result_deadline'])->endOfDay()
            : $startDateTime->copy()->addDays(7);

        if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
            $endDateTime->addDay();
        }

        $durationMinutes = $startDateTime->diffInMinutes($endDateTime);

        $selectedSubject = Subject::find($validated['subject_id']);
        $createdCount = 0;

        foreach ($validated['class_section_ids'] as $sectionId) {
            $section = ClassSection::find($sectionId);
            if (! $section) continue;

            $targetSubjectId = $validated['subject_id'];
            if ($selectedSubject && $selectedSubject->institute_class_id !== $section->institute_class_id) {
                $matchingSub = Subject::where('institute_class_id', $section->institute_class_id)
                    ->where('subject_name', $selectedSubject->subject_name)
                    ->first();
                if ($matchingSub) {
                    $targetSubjectId = $matchingSub->id;
                }
            }

            Assessment::create([
                'title' => $validated['title'],
                'type' => $type,
                'academic_term_id' => $termId,
                'class_section_id' => $sectionId,
                'subject_id' => $targetSubjectId,
                'creator_id' => $user->id,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'result_deadline' => $resultDeadlineTime,
                'has_time_limit' => true,
                'duration_minutes' => $durationMinutes,
                'total_marks' => $validated['total_marks'],
                'room' => $validated['room'] ?? 'Room 1',
                'is_published_teacher' => filter_var($validated['is_published_teacher'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'is_published_student' => filter_var($validated['is_published_student'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'instructions' => $validated['instructions'] ?? null,
                'status' => Assessment::STATUS_PUBLISHED,
                'is_marksheet_saved' => true,
                'saved_at' => now(),
            ]);

            $createdCount++;
        }

        if ($createdCount > 0) {
            try {
                $sections = \App\Models\ClassSection::whereIn('id', $validated['class_section_ids'])->get();
                foreach ($sections as $sec) {
                    \App\Services\PortalNotificationService::notifyStudentDatesheetPublished(
                        $instituteId,
                        $sec->id,
                        $validated['title'],
                        $sec->section_name
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to dispatch datesheet student notification: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Exam schedule '{$validated['title']}' published for {$createdCount} class section(s) on the Datesheet!");
    }

    /**
     * Update an individual Exam Entry on the Datesheet (Principal / Admin Only)
     */
    public function update(Request $request, int $id)
    {
        $user = auth()->user();
        if (! $user->isAdministration()) {
            return redirect()->back()->with('error', 'Unauthorized: Only Principal or Administration can edit Exam Datesheets.');
        }

        $assessment = Assessment::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'academic_term_id' => 'nullable|exists:academic_terms,id',
            'exam_date' => 'required|date',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'total_marks' => 'required|integer|min:1|max:1000',
            'room' => 'nullable|string|max:255',
            'is_published_teacher' => 'nullable',
            'is_published_student' => 'nullable',
            'result_deadline' => 'nullable|date',
            'instructions' => 'nullable|string|max:2000',
        ]);

        $startDateTime = Carbon::parse($validated['exam_date'].' '.$validated['start_time']);
        $endDateTime = Carbon::parse($validated['exam_date'].' '.$validated['end_time']);
        if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
            $endDateTime->addDay();
        }

        $durationMinutes = $startDateTime->diffInMinutes($endDateTime);
        $resultDeadlineTime = ! empty($validated['result_deadline'])
            ? Carbon::parse($validated['result_deadline'])->endOfDay()
            : $startDateTime->copy()->addDays(7);

        $assessment->update([
            'title' => $validated['title'],
            'type' => $this->normalizeEnumType($validated['type'] ?? null, $validated['title']),
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'duration_minutes' => $durationMinutes,
            'total_marks' => $validated['total_marks'],
            'room' => $validated['room'] ?? $assessment->room ?? 'Room 1',
            'is_published_teacher' => $request->boolean('is_published_teacher'),
            'is_published_student' => $request->boolean('is_published_student'),
            'result_deadline' => $resultDeadlineTime,
            'instructions' => $validated['instructions'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Exam paper schedule updated successfully on Datesheet.');
    }

    /**
     * Map arbitrary title/type string into valid MySQL assessment enum value
     */
    private function normalizeEnumType(?string $type, string $title = ''): string
    {
        $input = strtolower(trim(($type ?: $title)));
        if (str_contains($input, 'final')) {
            return 'final';
        } elseif (str_contains($input, 'quiz')) {
            return 'quiz';
        } elseif (str_contains($input, 'assign')) {
            return 'assignment';
        } elseif (str_contains($input, 'project')) {
            return 'project';
        } elseif (str_contains($input, 'present')) {
            return 'presentation';
        } elseif (str_contains($input, 'home')) {
            return 'homework';
        }
        return 'midterm';
    }

    /**
     * Delete All Exam Entries for a Specific Class Section (Principal / Admin Only)
     */
    public function destroyClassSectionDatesheet(int $classSectionId)
    {
        $user = auth()->user();
        if (! $user->isAdministration()) {
            return redirect()->back()->with('error', 'Unauthorized: Only Principal or Administration can remove Class Datesheets.');
        }

        $classSection = ClassSection::with('instituteClass')->findOrFail($classSectionId);
        $authorizedCampuses = method_exists($user, 'authorizedCampusIds')
            ? ($user->authorizedCampusIds() ?: [$user->institute_id])
            : [$user->institute_id];

        $sectionInstituteId = $classSection->instituteClass?->institute_id;
        abort_if(
            $sectionInstituteId !== $user->institute_id && ! in_array($sectionInstituteId, $authorizedCampuses, true),
            403,
            'Cross-tenant resource modification denied.'
        );

        $deleted = Assessment::where('class_section_id', $classSectionId)->delete();

        return redirect()->back()->with('success', "Entire datesheet ({$deleted} exam papers) for this class section has been removed.");
    }

    /**
     * Delete Exam Entry from Datesheet (Principal / Admin Only).
     * Hardened against Cross-Tenant Insecure Direct Object Reference (IDOR).
     */
    public function destroy(int|string $id)
    {
        $user = auth()->user();

        if (! $user->isAdministration() && ! $user->isPrincipal() && $user->role !== \App\Models\User::ROLE_PRINCIPAL) {
            abort(403, 'Unauthorized: Only Administration or Principal can remove Exam Datesheet entries.');
        }

        // Fetch without global scopes so cross-tenant checks are explicit
        $assessment = Assessment::withoutGlobalScopes()->findOrFail($id);

        // Resolve the institute this assessment belongs to (may be indirect via classSection)
        $assessmentInstituteId = $assessment->institute_id
            ?? $assessment->classSection?->institute_id
            ?? $assessment->classSection?->instituteClass?->institute_id;

        $authorizedCampuses = method_exists($user, 'authorizedCampusIds')
            ? ($user->authorizedCampusIds() ?: [$user->institute_id])
            : [$user->institute_id];

        abort_if(
            $assessmentInstituteId !== $user->institute_id && ! in_array($assessmentInstituteId, $authorizedCampuses, true),
            403,
            'Cross-tenant resource modification denied.'
        );

        $assessment->delete();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'message' => 'Exam schedule entry removed from Datesheet.']);
        }

        return redirect()->back()->with('success', 'Exam schedule entry removed from Datesheet.');
    }
}
