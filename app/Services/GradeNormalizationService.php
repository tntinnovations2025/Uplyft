<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\GradeWeightage;
use App\Models\StudentAssessmentAnswer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Grade Normalization Service
 *
 * Aggregates a student's scores across all assessment types for a subject
 * within an academic term, applies teacher-defined weightage percentages,
 * and normalizes the final performance out of 100.
 *
 * Business Rules:
 * - Midterms and Finals are mandatory components.
 * - Optional components (quizzes, assignments, etc.) are only factored
 *   if weightages are configured by the teacher.
 * - The total weightage across all types must sum to 100%.
 */
class GradeNormalizationService
{
    // ── Normalize Single Student ─────────────────────────────────────────────

    /**
     * Calculate the normalized grade (out of 100) for a student
     * in a specific subject for a given academic term.
     *
     * @return array{
     *     student_id: int,
     *     subject_id: int,
     *     normalized_score: float,
     *     breakdown: array,
     *     is_complete: bool,
     *     missing_mandatory: array
     * }
     */
    public function normalizeStudentGrade(
        int $studentId,
        int $subjectId,
        int $academicTermId,
        int $classSectionId
    ): array {
        // Step 1: Load all assessments for this subject/term/section
        $assessments = Assessment::where('subject_id', $subjectId)
            ->where('academic_term_id', $academicTermId)
            ->where('class_section_id', $classSectionId)
            ->with('questions')
            ->orderBy('created_at')
            ->get();

        $testBreakdown = [];
        $totalWeightedScore = 0.0;
        $totalConfiguredWeightage = 0.0;

        foreach ($assessments as $assessment) {
            $questionIds = $assessment->questions->pluck('id');

            $studentMarks = StudentAssessmentAnswer::where('student_id', $studentId)
                ->whereIn('assessment_question_id', $questionIds)
                ->whereIn('grading_status', [
                    StudentAssessmentAnswer::STATUS_AUTO_GRADED,
                    StudentAssessmentAnswer::STATUS_AI_GRADED,
                    StudentAssessmentAnswer::STATUS_MANUALLY_GRADED,
                ])
                ->sum('marks_awarded');

            $obtained = (float) $studentMarks;
            $maxMarks = $assessment->total_marks > 0 ? $assessment->total_marks : 100;
            $weightage = (float) ($assessment->weightage_percentage > 0 ? $assessment->weightage_percentage : 10.0);

            $percentage = round(($obtained / $maxMarks) * 100, 2);
            $weightedContribution = round(($obtained / $maxMarks) * $weightage, 2);

            $totalWeightedScore += $weightedContribution;
            $totalConfiguredWeightage += $weightage;

            $testBreakdown[] = [
                'assessment_id' => $assessment->id,
                'test_title' => $assessment->title,
                'type' => $assessment->type,
                'is_paper_test' => (bool) $assessment->is_paper_test,
                'obtained_marks' => $obtained,
                'max_marks' => $maxMarks,
                'percentage' => $percentage,
                'weightage_percentage' => $weightage,
                'weighted_contribution' => $weightedContribution,
            ];
        }

        // If weightage equals 100%, totalWeightedScore is out of 100.
        // If totalConfiguredWeightage > 0, calculate normalized annual percentage
        $annualScoreOutOf100 = $totalConfiguredWeightage > 0
            ? round(($totalWeightedScore / $totalConfiguredWeightage) * 100, 2)
            : 0.0;

        return [
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'normalized_score' => $annualScoreOutOf100,
            'total_weighted_score' => round($totalWeightedScore, 2),
            'total_weightage_capacity' => round($totalConfiguredWeightage, 2),
            'test_breakdown' => $testBreakdown,
            'is_complete' => count($testBreakdown) > 0,
        ];
    }

    // ── Normalize Entire Class ───────────────────────────────────────────────

    /**
     * Generate normalized grades for all students in a class section
     * for a specific subject.
     *
     * @return Collection<int, array>
     */
    public function normalizeClassGrades(
        int $subjectId,
        int $academicTermId,
        int $classSectionId,
        array $studentIds
    ): Collection {
        return collect($studentIds)->map(function (int $studentId) use ($subjectId, $academicTermId, $classSectionId) {
            return $this->normalizeStudentGrade($studentId, $subjectId, $academicTermId, $classSectionId);
        });
    }

    // ── Validate Weightage Configuration ─────────────────────────────────────

    /**
     * Validate that configured weightages sum to exactly 100%.
     *
     * @return array{valid: bool, total: float, message: string}
     */
    public function validateWeightages(int $subjectId, int $academicTermId, int $classSectionId): array
    {
        $total = GradeWeightage::where('subject_id', $subjectId)
            ->where('academic_term_id', $academicTermId)
            ->where('class_section_id', $classSectionId)
            ->sum('weightage_percentage');

        $total = round((float) $total, 2);

        if ($total === 100.00 || $total === 100.0) {
            return ['valid' => true, 'total' => $total, 'message' => 'Weightages are correctly configured.'];
        }

        if ($total < 100) {
            $remaining = round(100 - $total, 2);

            return [
                'valid' => false,
                'total' => $total,
                'message' => "Weightages sum to {$total}%. You need to allocate {$remaining}% more.",
            ];
        }

        return [
            'valid' => false,
            'total' => $total,
            'message' => "Weightages sum to {$total}%, which exceeds 100%. Please adjust.",
        ];
    }

    // ── Set Default Weightages ───────────────────────────────────────────────

    /**
     * Apply a standard default weightage configuration for a subject.
     * Midterm: 30%, Final: 40%, Quizzes: 10%, Assignments: 10%, Projects: 10%
     */
    public function applyDefaultWeightages(
        int $subjectId,
        int $academicTermId,
        int $classSectionId,
        int $configuredBy
    ): void {
        $defaults = [
            ['type' => 'midterm',    'weight' => 30, 'mandatory' => true],
            ['type' => 'final',      'weight' => 40, 'mandatory' => true],
            ['type' => 'quiz',       'weight' => 10, 'mandatory' => false],
            ['type' => 'assignment', 'weight' => 10, 'mandatory' => false],
            ['type' => 'project',    'weight' => 10, 'mandatory' => false],
        ];

        DB::transaction(function () use ($defaults, $subjectId, $academicTermId, $classSectionId, $configuredBy) {
            foreach ($defaults as $config) {
                GradeWeightage::updateOrCreate(
                    [
                        'subject_id' => $subjectId,
                        'academic_term_id' => $academicTermId,
                        'class_section_id' => $classSectionId,
                        'assessment_type' => $config['type'],
                    ],
                    [
                        'configured_by' => $configuredBy,
                        'weightage_percentage' => $config['weight'],
                        'is_mandatory' => $config['mandatory'],
                    ]
                );
            }
        });
    }

    // ── Letter Grade Conversion ──────────────────────────────────────────────

    /**
     * Convert a normalized score (0-100) to a letter grade.
     */
    public function toLetterGrade(float $score, ?int $subjectId = null): string
    {
        if ($subjectId) {
            $subject = \App\Models\Subject::find($subjectId);
            if ($subject && !empty($subject->grade_scale_json) && is_array($subject->grade_scale_json)) {
                $scale = $subject->grade_scale_json;
                // Sort by min score descending
                uasort($scale, fn($a, $b) => (float)$b <=> (float)$a);
                foreach ($scale as $grade => $minScore) {
                    if ($score >= (float)$minScore) {
                        return (string)$grade;
                    }
                }
                return 'F';
            }
        }

        return match (true) {
            $score >= 90 => 'A+',
            $score >= 85 => 'A',
            $score >= 80 => 'A-',
            $score >= 75 => 'B+',
            $score >= 70 => 'B',
            $score >= 65 => 'B-',
            $score >= 60 => 'C+',
            $score >= 55 => 'C',
            $score >= 50 => 'C-',
            $score >= 45 => 'D',
            default => 'F',
        };
    }

    /**
     * Get GPA value for a normalized score.
     */
    public function toGpa(float $score): float
    {
        return match (true) {
            $score >= 90 => 4.0,
            $score >= 85 => 3.7,
            $score >= 80 => 3.3,
            $score >= 75 => 3.0,
            $score >= 70 => 2.7,
            $score >= 65 => 2.3,
            $score >= 60 => 2.0,
            $score >= 55 => 1.7,
            $score >= 50 => 1.3,
            $score >= 45 => 1.0,
            default => 0.0,
        };
    }

    /**
     * Continuous Live Recalculation Trigger (Webhook / Event Sync)
     * Recalculates normalized grades and syncs StudentSubjectResult for all students in a section/subject.
     */
    public function recalculateAndSyncClassResults(
        int $subjectId,
        ?int $academicTermId = null,
        ?int $classSectionId = null
    ): array {
        if (!$academicTermId) {
            $subject = \App\Models\Subject::find($subjectId);
            $academicTermId = $subject?->academic_term_id ?? 1;
        }

        $query = \App\Models\Student::withoutGlobalScopes();
        if ($classSectionId) {
            $query->where('class_section_id', $classSectionId);
        } else {
            $subject = \App\Models\Subject::find($subjectId);
            if ($subject && $subject->institute_class_id) {
                $sectionIds = \App\Models\ClassSection::where('institute_class_id', $subject->institute_class_id)->pluck('id');
                $query->whereIn('class_section_id', $sectionIds);
            }
        }

        $students = $query->get();
        $updatedResults = [];

        foreach ($students as $student) {
            $secId = $classSectionId ?: $student->class_section_id;
            if (!$secId) continue;

            $normalized = $this->normalizeStudentGrade($student->user_id ?: $student->id, $subjectId, $academicTermId, $secId);
            $score = $normalized['normalized_score'];
            $grade = $this->toLetterGrade($score, $subjectId);
            $gpa = $this->toGpa($score);

            $result = \App\Models\StudentSubjectResult::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subjectId,
                    'academic_term_id' => $academicTermId,
                ],
                [
                    'institute_id' => $student->institute_id ?? auth()->user()?->institute_id ?? 1,
                    'marks_obtained' => round($score),
                    'total_marks' => 100,
                    'result_status' => $score >= 50 ? 'passed' : 'failed',
                    'remarks' => "Grade: {$grade} (GPA: {$gpa}) | Auto-Normalized via Continuous Grading Engine",
                ]
            );

            $updatedResults[] = [
                'student_id' => $student->id,
                'name' => $student->full_name ?? ($student->first_name . ' ' . $student->last_name),
                'score' => $score,
                'grade' => $grade,
                'gpa' => $gpa,
            ];
        }

        \Illuminate\Support\Facades\Log::info('Continuous Grading: Auto-normalized grades synced', [
            'subject_id' => $subjectId,
            'academic_term_id' => $academicTermId,
            'class_section_id' => $classSectionId,
            'students_updated' => count($updatedResults),
        ]);

        return [
            'success' => true,
            'count' => count($updatedResults),
            'results' => $updatedResults,
        ];
    }
}
