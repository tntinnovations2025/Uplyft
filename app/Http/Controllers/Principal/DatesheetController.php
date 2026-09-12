<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Assessment;
use App\Models\ClassSection;
use App\Models\Datesheet;
use App\Models\Room;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Principal Datesheet Management Controller
 *
 * Hardened against cross-tenant modification and deletion (BUG-LMS-002).
 */
class DatesheetController extends Controller
{
    /**
     * Display the Official Exam Datesheet Directory
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isStudent()) {
            return redirect()->route('student.datesheet');
        }

        $instituteId = $user->institute_id;

        $academicTerms = AcademicTerm::where('institute_id', $instituteId)->get();
        if ($academicTerms->isEmpty()) {
            $academicTerms = AcademicTerm::all();
        }

        $classSections = ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->with(['instituteClass', 'subjects'])
            ->get()
            ->sortBy(fn ($cs) => ($cs->instituteClass?->custom_name ?? '').' '.$cs->section_name);

        $classIds = $classSections->pluck('institute_class_id')->filter()->unique();
        $allClassSubjects = Subject::whereIn('institute_class_id', $classIds)->get()->groupBy('institute_class_id');

        foreach ($classSections as $cs) {
            if ($cs->subjects->isEmpty() && $cs->institute_class_id) {
                $cs->setRelation('subjects', $allClassSubjects->get($cs->institute_class_id, collect()));
            }
        }

        $subjects = Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->orderBy('subject_name')
            ->get()
            ->unique('subject_name');

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

        $groupedDatesheet = $datesheetEntries->groupBy(function ($item) {
            $cName = $item->classSection?->instituteClass?->custom_name ?? 'Class';
            $sName = $item->classSection?->section_name ?? '';
            return trim("{$cName} - {$sName}");
        });

        $registeredRooms = Room::where('institute_id', $instituteId)
            ->orderBy('room_number')
            ->get();

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
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_if(
            ! $user->isAdministration() && ! $user->isPrincipal(),
            403,
            'Unauthorized: Only Principal or Administration can create Exam Datesheets.'
        );

        $instituteId = $user->institute_id;
        $authorizedCampuses = method_exists($user, 'authorizedCampusIds')
            ? ($user->authorizedCampusIds() ?: [$instituteId])
            : [$instituteId];

        // Multi-class batch schedule payload
        if ($request->has('class_schedules') && is_array($request->class_schedules)) {
            $createdCount = 0;
            $title = $request->input('title', 'Midterm Examination');
            $typeInput = $request->input('type') ?: $title;
            $enumType = $this->normalizeEnumType($typeInput, $title);
            $termId = $request->input('academic_term_id');

            foreach ($request->class_schedules as $sectionId => $papers) {
                if (! is_array($papers)) continue;

                // Verify section belongs to this institute
                $section = ClassSection::with('instituteClass')->find($sectionId);
                if (! $section || ! in_array($section->instituteClass?->institute_id, $authorizedCampuses, true)) {
                    continue;
                }

                foreach ($papers as $paper) {
                    if (empty($paper['subject_id']) || empty($paper['exam_date'])) continue;

                    $subject = Subject::with('instituteClass')->find($paper['subject_id']);
                    if (! $subject || ! in_array($subject->instituteClass?->institute_id, $authorizedCampuses, true)) {
                        continue;
                    }

                    $startDateTime = Carbon::parse($paper['exam_date'].' '.($paper['start_time'] ?? '09:00'));
                    $endDateTime = Carbon::parse($paper['exam_date'].' '.($paper['end_time'] ?? '11:30'));
                    if ($endDateTime->lessThanOrEqualTo($startDateTime)) {
                        $endDateTime->addDay();
                    }

                    $durationMinutes = $startDateTime->diffInMinutes($endDateTime);
                    $resultDeadlineTime = ! empty($paper['result_deadline'])
                        ? Carbon::parse($paper['result_deadline'])->endOfDay()
                        : $startDateTime->copy()->addDays(7);

                    Assessment::updateOrCreate(
                        [
                            'class_section_id' => $sectionId,
                            'subject_id'       => $paper['subject_id'],
                            'title'            => $title,
                        ],
                        [
                            'type'                 => $enumType,
                            'academic_term_id'     => $termId,
                            'creator_id'           => $user->id,
                            'start_time'           => $startDateTime,
                            'end_time'             => $endDateTime,
                            'result_deadline'      => $resultDeadlineTime,
                            'has_time_limit'       => true,
                            'duration_minutes'     => $durationMinutes,
                            'total_marks'          => (int) ($paper['total_marks'] ?? 100),
                            'room'                 => $paper['room'] ?? $request->input('room', 'Unassigned'),
                            'is_published_teacher' => true,
                            'is_published_student' => true,
                            'instructions'         => $paper['instructions'] ?? $request->input('instructions'),
                            'status'               => Assessment::STATUS_PUBLISHED,
                            'is_marksheet_saved'   => true,
                            'saved_at'             => now(),
                        ]
                    );

                    $createdCount++;
                }
            }

            return redirect()->back()->with('success', "Official Exam Datesheet published for {$createdCount} scheduled exam paper(s)!");
        }

        return redirect()->back()->with('error', 'Invalid payload for datesheet publishing.');
    }

    /**
     * Delete Exam Entry from Datesheet.
     * Strictly enforces multi-tenant boundary checks (BUG-LMS-002).
     */
    public function destroy(Datesheet|Assessment|int $datesheet): RedirectResponse
    {
        $user = Auth::user();

        abort_if(
            ! $user->isAdministration() && ! $user->isPrincipal(),
            403,
            'Unauthorized: Only Principal or Administration can remove Exam Datesheet entries.'
        );

        // Resolve model instance if integer ID was provided
        if (is_numeric($datesheet)) {
            $datesheet = Assessment::findOrFail((int) $datesheet);
        }

        $authorizedCampuses = method_exists($user, 'authorizedCampusIds')
            ? ($user->authorizedCampusIds() ?: [$user->institute_id])
            : [$user->institute_id];

        // Strict Tenant Isolation Verification
        $resourceInstituteId = $datesheet->institute_id;
        abort_if(
            $resourceInstituteId !== $user->institute_id && ! in_array($resourceInstituteId, $authorizedCampuses, true),
            403,
            'Cross-tenant resource modification denied.'
        );

        $datesheet->delete();

        return redirect()->back()->with('success', 'Exam schedule entry removed from Datesheet.');
    }

    /**
     * Delete All Exam Entries for a Specific Class Section.
     * Enforces tenant verification on classSection (BUG-LMS-002).
     */
    public function destroyClassSectionDatesheet(int $classSectionId): RedirectResponse
    {
        $user = Auth::user();

        abort_if(
            ! $user->isAdministration() && ! $user->isPrincipal(),
            403,
            'Unauthorized: Only Principal or Administration can remove Class Datesheets.'
        );

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
     * Toggle Portal Visibility for Class Section Datesheet.
     */
    public function toggleClassVisibility(Request $request, int $classSectionId): RedirectResponse
    {
        $user = Auth::user();

        abort_if(
            ! $user->isAdministration() && ! $user->isPrincipal(),
            403,
            'Unauthorized: Only Principal or Administration can modify portal visibility.'
        );

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

        $target = $request->input('target');
        $assessments = Assessment::where('class_section_id', $classSectionId)->get();

        if ($assessments->isEmpty()) {
            return redirect()->back()->with('error', 'No exam entries found for this class section.');
        }

        if ($target === 'teacher') {
            $newStatus = ! $assessments->first()->is_published_teacher;
            Assessment::where('class_section_id', $classSectionId)->update(['is_published_teacher' => $newStatus]);
            $statusText = $newStatus ? 'Visible' : 'Hidden';
            return redirect()->back()->with('success', "Teacher Portal visibility set to '{$statusText}'.");
        } elseif ($target === 'student') {
            $newStatus = ! $assessments->first()->is_published_student;
            Assessment::where('class_section_id', $classSectionId)->update(['is_published_student' => $newStatus]);
            $statusText = $newStatus ? 'Visible' : 'Hidden';
            return redirect()->back()->with('success', "Student Portal visibility set to '{$statusText}'.");
        }

        return redirect()->back();
    }

    private function normalizeEnumType(string $type, string $title): string
    {
        $candidate = strtolower(trim($type));
        $valid = ['midterm', 'final', 'quiz', 'assignment', 'project', 'homework', 'presentation'];

        if (in_array($candidate, $valid, true)) {
            return $candidate;
        }

        $titleLower = strtolower($title);
        if (str_contains($titleLower, 'final')) return 'final';
        if (str_contains($titleLower, 'quiz')) return 'quiz';
        if (str_contains($titleLower, 'assign')) return 'assignment';
        if (str_contains($titleLower, 'project')) return 'project';

        return 'midterm';
    }
}
