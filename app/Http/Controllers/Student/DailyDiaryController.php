<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\DailyDiary;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DailyDiaryController extends Controller
{
    /**
     * Display the Student's Daily Diary.
     * Structured timeline of homework, tests, and updates grouped by Day and Date.
     * Strictly read-only for students.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_if(!$user, 401, 'Unauthenticated.');

        $student = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->with(['classSection.instituteClass'])
            ->first();

        if (!$student && $user->email) {
            $student = Student::withoutGlobalScopes()
                ->where('email', $user->email)
                ->with(['classSection.instituteClass'])
                ->first();
        }

        if (!$student) {
            abort(403, 'No student profile linked to your account. Please contact campus administration.');
        }

        $sectionId = $student->class_section_id;

        // Fetch student's enrolled or class subjects
        $subjects = collect();
        if ($sectionId) {
            $allocations = TeacherSubjectSection::where('class_section_id', $sectionId)
                ->with('subject')
                ->get();

            $subjects = $allocations->pluck('subject')->filter()->unique('id');

            if ($subjects->isEmpty() && $student->classSection) {
                $subjects = Subject::where('institute_class_id', $student->classSection->institute_class_id)->get();
            }

            $enrolledSubjectIds = StudentSubjectEnrollment::where('student_id', $student->user_id ?? $user->id)
                ->where('enrollment_status', 'active')
                ->pluck('subject_id')
                ->all();

            if (!empty($enrolledSubjectIds)) {
                $subjects = $subjects->whereIn('id', $enrolledSubjectIds)->values();
            }
        }

        $selectedSubjectId = $request->input('subject_id');
        $selectedType = $request->input('type');

        // Fetch Diary Entries for this class section
        $query = DailyDiary::where('class_section_id', $sectionId)
            ->where('is_active', true)
            ->with(['subject', 'teacher', 'classSection.instituteClass']);

        if ($selectedSubjectId) {
            $query->where('subject_id', $selectedSubjectId);
        }

        if ($selectedType && $selectedType !== 'all') {
            $query->where('entry_type', $selectedType);
        }

        $allEntries = $query->orderBy('assigned_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Group entries by Day & Date (e.g. "Wednesday, September 19, 2026")
        $groupedEntries = $allEntries->groupBy(function ($diary) {
            $date = $diary->assigned_date ? Carbon::parse($diary->assigned_date) : Carbon::today();
            return $date->format('l, F j, Y');
        });

        // Subject counts map for badge indicators
        $subjectCounts = DailyDiary::where('class_section_id', $sectionId)
            ->where('is_active', true)
            ->selectRaw('subject_id, count(*) as count')
            ->groupBy('subject_id')
            ->pluck('count', 'subject_id');

        $activeSubject = $selectedSubjectId ? $subjects->firstWhere('id', (int) $selectedSubjectId) : null;

        return view('student.diary.index', compact(
            'student',
            'subjects',
            'groupedEntries',
            'subjectCounts',
            'selectedSubjectId',
            'selectedType',
            'activeSubject',
            'allEntries'
        ));
    }
}
