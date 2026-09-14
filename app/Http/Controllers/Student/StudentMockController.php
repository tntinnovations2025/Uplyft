<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSubmission;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentMockController extends Controller
{
    /**
     * Display all scheduled mock examinations available to the student with live schedule gating.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        // 1. Resolve Enrolled Subjects for the student
        $enrolledSubjectIds = StudentSubjectEnrollment::where('student_id', $user->id)
            ->pluck('subject_id')
            ->toArray();

        if ($student && !empty($student->selected_subject_ids) && is_array($student->selected_subject_ids)) {
            $enrolledSubjectIds = array_unique(array_merge($enrolledSubjectIds, $student->selected_subject_ids));
        }

        // If no explicit enrollments yet, fallback to all subjects of the student's class
        if (empty($enrolledSubjectIds) && $student && $student->class_section_id) {
            $classSection = $student->classSection;
            if ($classSection && $classSection->institute_class_id) {
                $enrolledSubjectIds = Subject::where('institute_class_id', $classSection->institute_class_id)
                    ->pluck('id')
                    ->toArray();
            }
        }

        // 2. Query Mock Assessments
        $mocksQuery = Assessment::where('is_mock', true)
            ->where(function ($q) {
                $q->where('is_published', true)
                    ->orWhere('status', Assessment::STATUS_PUBLISHED)
                    ->orWhere('is_published_student', true);
            })
            ->where(function ($q) use ($user) {
                $q->whereHas('subject.instituteClass', function ($sq) use ($user) {
                    $sq->where('institute_id', $user->institute_id);
                });
            });

        if (!empty($enrolledSubjectIds)) {
            $mocksQuery->whereIn('subject_id', $enrolledSubjectIds);
        }

        // Class Section scoping (either assigned to whole class or student's specific section)
        if ($student && $student->class_section_id) {
            $studentSectionId = $student->class_section_id;
            $mocksQuery->where(function ($q) use ($studentSectionId) {
                $q->whereNull('class_section_id')
                    ->orWhere('class_section_id', $studentSectionId);
            });
        }

        $mocks = $mocksQuery->with(['subject.instituteClass', 'classSection', 'teacher', 'creator', 'questions'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // 3. Eager load submissions for the student
        $submissions = AssessmentSubmission::where('student_id', $user->id)
            ->whereIn('assessment_id', $mocks->pluck('id'))
            ->get()
            ->keyBy('assessment_id');

        $now = now();

        $stats = [
            'total' => $mocks->count(),
            'active' => 0,
            'locked' => 0,
            'completed' => 0,
            'expired' => 0,
        ];

        // 4. Compute State Gating for Each Mock
        foreach ($mocks as $mock) {
            $submission = $submissions->get($mock->id);
            $mock->submission_record = $submission;

            // Resolve scheduled start and end datetimes
            $startDate = $mock->scheduled_date ? Carbon::parse($mock->scheduled_date)->toDateString() : ($mock->start_time ? $mock->start_time->toDateString() : $now->toDateString());
            
            $startDateTime = $mock->start_time 
                ? ($mock->start_time->format('Y-m-d') === $startDate ? $mock->start_time : Carbon::parse("{$startDate} " . $mock->start_time->format('H:i:s')))
                : Carbon::parse("{$startDate} 00:00:00");

            $endDateTime = $mock->end_time
                ? ($mock->end_time->format('Y-m-d') === $startDate ? $mock->end_time : Carbon::parse("{$startDate} " . $mock->end_time->format('H:i:s')))
                : (clone $startDateTime)->addMinutes($mock->duration_minutes ?: 45);

            $mock->resolved_start_time = $startDateTime;
            $mock->resolved_end_time = $endDateTime;

            if ($submission && $submission->isCompleted()) {
                $mock->schedule_state = 'completed';
                $mock->state_badge = 'Completed';
                $stats['completed']++;
            } elseif ($now->gt($endDateTime)) {
                $mock->schedule_state = 'expired';
                $mock->state_badge = 'Missed / Closed';
                $stats['expired']++;
            } elseif ($now->lt($startDateTime)) {
                $mock->schedule_state = 'locked';
                $mock->state_badge = 'Locked';
                $stats['locked']++;

                // Humanized remaining time until start
                $diffHours = $now->diffInHours($startDateTime);
                $diffMinutes = $now->diffInMinutes($startDateTime) % 60;
                if ($diffHours > 24) {
                    $mock->countdown_string = 'Opens on ' . $startDateTime->format('M d \a\t h:i A');
                } elseif ($diffHours > 0) {
                    $mock->countdown_string = "Opens in {$diffHours}h {$diffMinutes}m";
                } else {
                    $mock->countdown_string = "Opens in {$diffMinutes}m";
                }
            } else {
                $mock->schedule_state = 'active';
                $mock->state_badge = 'Active & Ready';
                $stats['active']++;
            }
        }

        return view('student.mocks.index', compact('mocks', 'student', 'stats'));
    }

    /**
     * Enter and render the distraction-free Examination Workspace with Alpine.js countdown timer.
     */
    public function take(Assessment $assessment): View|RedirectResponse
    {
        $user = auth()->user();

        // 1. Authorization check: Institute boundary
        if ($assessment->institute_id && $assessment->institute_id != $user->institute_id) {
            abort(403, 'Unauthorized access to this examination.');
        }

        // 2. Check if already completed -> redirect to result
        $submission = AssessmentSubmission::where('assessment_id', $assessment->id)
            ->where('student_id', $user->id)
            ->first();

        if ($submission && $submission->isCompleted()) {
            return redirect()->route('student.mocks.result', [
                'assessment' => $assessment->id,
                'submission' => $submission->id,
            ]);
        }

        // 3. Live Schedule Gating Enforcement
        $now = now();
        $startDate = $assessment->scheduled_date ? Carbon::parse($assessment->scheduled_date)->toDateString() : ($assessment->start_time ? $assessment->start_time->toDateString() : $now->toDateString());
        
        $startDateTime = $assessment->start_time 
            ? Carbon::parse("{$startDate} " . $assessment->start_time->format('H:i:s'))
            : Carbon::parse("{$startDate} 00:00:00");

        $endDateTime = $assessment->end_time
            ? Carbon::parse("{$startDate} " . $assessment->end_time->format('H:i:s'))
            : (clone $startDateTime)->addMinutes($assessment->duration_minutes ?: 45);

        if ($now->lt($startDateTime)) {
            return redirect()->route('student.mocks.index')->with('error', "This mock exam is locked. It opens on {$startDateTime->format('D, M d, Y \a\\t h:i A')}.");
        }

        if ($now->gt($endDateTime) && (!$submission || $submission->status !== AssessmentSubmission::STATUS_IN_PROGRESS)) {
            return redirect()->route('student.mocks.index')->with('error', 'The examination cutoff window has closed.');
        }

        // 4. Initialize or retrieve In-Progress Submission Record
        if (!$submission) {
            $submission = AssessmentSubmission::create([
                'assessment_id' => $assessment->id,
                'student_id' => $user->id,
                'started_at' => now(),
                'status' => AssessmentSubmission::STATUS_IN_PROGRESS,
                'total_score' => 0.00,
            ]);
        }

        // 5. Calculate Exact Remaining Seconds for Timer
        $durationSeconds = ($assessment->duration_minutes ?: 45) * 60;
        $elapsedSinceStart = $submission->started_at ? now()->diffInSeconds($submission->started_at) : 0;
        $remainingFromDuration = max(1, $durationSeconds - $elapsedSinceStart);
        $remainingToCutoff = max(1, now()->diffInSeconds($endDateTime, false));

        $remainingSeconds = min($remainingFromDuration, $remainingToCutoff);

        $assessment->load([
            'subject.instituteClass',
            'questions' => function ($q) {
                $q->orderBy('sort_order');
            }
        ]);

        return view('student.mocks.take', compact('assessment', 'submission', 'remainingSeconds'));
    }

    /**
     * Submit Examination: Instant Server-Side Grading Engine.
     */
    public function submit(Request $request, Assessment $assessment): RedirectResponse
    {
        $user = auth()->user();

        // 1. Authorization check
        if ($assessment->institute_id && $assessment->institute_id != $user->institute_id) {
            abort(403, 'Unauthorized access.');
        }

        $submission = AssessmentSubmission::where('assessment_id', $assessment->id)
            ->where('student_id', $user->id)
            ->first();

        if ($submission && $submission->isCompleted()) {
            return redirect()->route('student.mocks.result', [
                'assessment' => $assessment->id,
                'submission' => $submission->id,
            ]);
        }

        if (!$submission) {
            $submission = new AssessmentSubmission([
                'assessment_id' => $assessment->id,
                'student_id' => $user->id,
                'started_at' => now(),
            ]);
        }

        // 2. Read Submitted Answers
        $submittedAnswers = (array) $request->input('answers', []);

        // 3. Instant Server-Side Evaluation Pipeline against assessment_questions.correct_answer
        $assessment->load('questions');
        $score = 0;

        foreach ($assessment->questions as $question) {
            $selected = $submittedAnswers[$question->id] ?? null;
            if ($selected !== null && strtoupper(trim((string)$selected)) === strtoupper(trim((string)$question->correct_answer))) {
                $score += 1;
            }
        }

        // 4. Update Submission State
        $isAutoSubmit = $request->boolean('is_auto_submit') || $request->input('status') === 'auto_submitted';

        $submission->answers = $submittedAnswers;
        $submission->total_score = $score;
        $submission->status = $isAutoSubmit ? AssessmentSubmission::STATUS_AUTO_SUBMITTED : AssessmentSubmission::STATUS_COMPLETED;
        $submission->submitted_at = now();
        $submission->save();

        return redirect()->route('student.mocks.result', [
            'assessment' => $assessment->id,
            'submission' => $submission->id,
        ])->with('success', 'Examination submitted and evaluated successfully!');
    }

    /**
     * Display Instant Exam Results & Question Breakdown with Textbook Citations.
     */
    public function result(Assessment $assessment, AssessmentSubmission $submission): View
    {
        $user = auth()->user();

        // Security check
        if ($submission->student_id !== $user->id || $submission->assessment_id !== $assessment->id) {
            abort(403, 'Unauthorized access to this examination result.');
        }

        $assessment->load([
            'subject.instituteClass',
            'teacher',
            'questions' => function ($q) {
                $q->orderBy('sort_order');
            }
        ]);

        $submittedAnswers = (array) ($submission->answers ?: []);
        $totalQuestions = $assessment->questions->count();
        $attempted = 0;
        $correct = 0;
        $incorrect = 0;
        $unanswered = 0;

        $reviewQuestions = [];

        foreach ($assessment->questions as $q) {
            $userChoice = $submittedAnswers[$q->id] ?? null;
            $hasAnswered = !empty($userChoice);
            $isCorrect = $hasAnswered && (strtoupper(trim((string)$userChoice)) === strtoupper(trim((string)$q->correct_answer)));

            if (!$hasAnswered) {
                $unanswered++;
            } elseif ($isCorrect) {
                $correct++;
                $attempted++;
            } else {
                $incorrect++;
                $attempted++;
            }

            $reviewQuestions[] = [
                'question' => $q,
                'user_choice' => $userChoice,
                'is_correct' => $isCorrect,
                'is_unanswered' => !$hasAnswered,
                'correct_answer' => $q->correct_answer,
                'citation' => $q->chapter_reference,
                'explanation' => $q->explanation,
            ];
        }

        $score = (float) $submission->total_score;
        $percentage = $totalQuestions > 0 ? round(($score / $totalQuestions) * 100, 1) : 0.0;

        // Grade label
        if ($percentage >= 90) {
            $grade = 'A*';
            $gradeColor = '#2E6E42';
            $gradeBadge = 'Distinction';
        } elseif ($percentage >= 80) {
            $grade = 'A';
            $gradeColor = '#2E6E42';
            $gradeBadge = 'Excellent';
        } elseif ($percentage >= 70) {
            $grade = 'B';
            $gradeColor = '#3A529C';
            $gradeBadge = 'Good Standing';
        } elseif ($percentage >= 60) {
            $grade = 'C';
            $gradeColor = '#D48A2E';
            $gradeBadge = 'Credit';
        } elseif ($percentage >= 50) {
            $grade = 'D';
            $gradeColor = '#8A5A10';
            $gradeBadge = 'Pass';
        } else {
            $grade = 'U';
            $gradeColor = '#A2412C';
            $gradeBadge = 'Ungraded';
        }

        return view('student.mocks.result', compact(
            'assessment',
            'submission',
            'reviewQuestions',
            'totalQuestions',
            'attempted',
            'correct',
            'incorrect',
            'unanswered',
            'score',
            'percentage',
            'grade',
            'gradeColor',
            'gradeBadge'
        ));
    }
}
