<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\GradeWeightage;
use App\Services\GradeNormalizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Grade Controller
 *
 * Manages grade weightage configuration and normalized report generation.
 */
class GradeController extends Controller
{
    protected GradeNormalizationService $normService;

    public function __construct(GradeNormalizationService $normService)
    {
        $this->normService = $normService;
    }

    // ── Page Views ────────────────────────────────────────────────────────────

    /**
     * Render the Grade Weightages configuration page.
     */
    public function weightagesPage()
    {
        $instituteId = auth()->user()->institute_id;
        $classes = \App\Models\InstituteClass::where('institute_id', $instituteId)
            ->with(['subjects', 'sections'])
            ->orderBy('custom_name')
            ->get();
        $terms = \App\Models\AcademicTerm::where('institute_id', $instituteId)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('lms.grades.weightages', compact('classes', 'terms'));
    }

    /**
     * Render the Grade Reports page.
     */
    public function reportPage()
    {
        return view('lms.grades.report');
    }

    // ── Weightage Configuration ──────────────────────────────────────────────

    /**
     * View current weightage configuration for a subject/term/section.
     */
    public function getWeightages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'required|exists:class_sections,id',
        ]);

        $subject = \App\Models\Subject::find($validated['subject_id']);

        $weightages = GradeWeightage::where('subject_id', $validated['subject_id'])
            ->where('academic_term_id', $validated['academic_term_id'])
            ->where('class_section_id', $validated['class_section_id'])
            ->get();

        $validation = $this->normService->validateWeightages(
            $validated['subject_id'],
            $validated['academic_term_id'],
            $validated['class_section_id'],
        );

        return response()->json([
            'subject' => $subject,
            'weightages' => $weightages,
            'validation' => $validation,
        ]);
    }

    /**
     * Update subject configuration: Total Marks, Grade Scale, and Student Display Toggles.
     */
    public function updateSubjectConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'total_marks' => 'required|integer|min:1',
            'mcq_weightage' => 'nullable|numeric|min:0|max:100',
            'short_answer_weightage' => 'nullable|numeric|min:0|max:100',
            'long_answer_weightage' => 'nullable|numeric|min:0|max:100',
            'assignment_quiz_weightage' => 'nullable|numeric|min:0|max:100',
            'show_marks_to_student' => 'required|boolean',
            'show_grade_to_student' => 'required|boolean',
            'grade_scale_json' => 'nullable|array',
        ]);

        $subject = \App\Models\Subject::findOrFail($validated['subject_id']);
        $subject->update([
            'total_marks' => $validated['total_marks'],
            'mcq_weightage' => $validated['mcq_weightage'] ?? $subject->mcq_weightage,
            'short_answer_weightage' => $validated['short_answer_weightage'] ?? $subject->short_answer_weightage,
            'long_answer_weightage' => $validated['long_answer_weightage'] ?? $subject->long_answer_weightage,
            'assignment_quiz_weightage' => $validated['assignment_quiz_weightage'] ?? $subject->assignment_quiz_weightage,
            'show_marks_to_student' => (bool)$validated['show_marks_to_student'],
            'show_grade_to_student' => (bool)$validated['show_grade_to_student'],
            'grade_scale_json' => $validated['grade_scale_json'] ?? [
                'A+' => 90, 'A' => 80, 'B' => 70, 'C' => 60, 'D' => 50, 'F' => 0
            ],
        ]);

        // Continuous Live Grade Recalculation Trigger
        try {
            $this->normService->recalculateAndSyncClassResults($subject->id, $subject->academic_term_id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Continuous Grading trigger failed: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Subject grading & display settings successfully updated!',
            'subject' => $subject,
        ]);
    }

    /**
     * Save or update weightage percentages (teacher action).
     */
    public function saveWeightages(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'weightages' => 'required|array|min:1',
            'weightages.*.assessment_type' => 'required|string|in:'.implode(',', Assessment::TYPES),
            'weightages.*.total_marks' => 'nullable|integer|min:0|max:1000',
            'weightages.*.weightage_percentage' => 'required|numeric|min:0|max:100',
            'weightages.*.is_mandatory' => 'nullable|boolean',
        ]);

        foreach ($validated['weightages'] as $w) {
            GradeWeightage::updateOrCreate(
                [
                    'subject_id' => $validated['subject_id'],
                    'academic_term_id' => $validated['academic_term_id'],
                    'class_section_id' => $validated['class_section_id'],
                    'assessment_type' => $w['assessment_type'],
                ],
                [
                    'configured_by' => auth()->id(),
                    'total_marks' => $w['total_marks'] ?? 100,
                    'weightage_percentage' => $w['weightage_percentage'],
                    'is_mandatory' => $w['is_mandatory'] ?? false,
                ]
            );
        }

        // Validate total
        $validation = $this->normService->validateWeightages(
            $validated['subject_id'],
            $validated['academic_term_id'],
            $validated['class_section_id'],
        );

        // Continuous Live Grade Recalculation Trigger
        try {
            $this->normService->recalculateAndSyncClassResults(
                $validated['subject_id'],
                $validated['academic_term_id'],
                $validated['class_section_id']
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Continuous Grading trigger failed: '.$e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Weightages saved.',
                'validation' => $validation,
            ]);
        }

        return redirect()->back()->with(
            $validation['valid'] ? 'success' : 'warning',
            "Weightages saved. {$validation['message']}"
        );
    }

    /**
     * Apply the default weightage template.
     */
    public function applyDefaults(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'required|exists:class_sections,id',
        ]);

        $this->normService->applyDefaultWeightages(
            $validated['subject_id'],
            $validated['academic_term_id'],
            $validated['class_section_id'],
            auth()->id(),
        );

        // Continuous Live Grade Recalculation Trigger
        try {
            $this->normService->recalculateAndSyncClassResults(
                $validated['subject_id'],
                $validated['academic_term_id'],
                $validated['class_section_id']
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Continuous Grading trigger failed: '.$e->getMessage());
        }

        return response()->json(['message' => 'Default weightages applied (Midterm 30%, Final 40%, Quiz 10%, Assignment 10%, Project 10%).']);
    }

    // ── Grade Reports ────────────────────────────────────────────────────────

    /**
     * Get normalized grade report for a single student.
     */
    public function studentReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'required|exists:class_sections,id',
        ]);

        $subject = \App\Models\Subject::find($validated['subject_id']);

        $report = $this->normService->normalizeStudentGrade(
            $validated['student_id'],
            $validated['subject_id'],
            $validated['academic_term_id'],
            $validated['class_section_id'],
        );

        // Enrich with letter grade and GPA using dynamic subject grade scale
        $report['letter_grade'] = $this->normService->toLetterGrade($report['normalized_score'], $subject?->id);
        $report['gpa'] = $this->normService->toGpa($report['normalized_score']);
        $report['show_marks_to_student'] = (bool) ($subject?->show_marks_to_student ?? true);
        $report['show_grade_to_student'] = (bool) ($subject?->show_grade_to_student ?? true);

        return response()->json(['report' => $report]);
    }

    /**
     * Get normalized grade report for an entire class section.
     */
    public function classReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
        ]);

        $reports = $this->normService->normalizeClassGrades(
            $validated['subject_id'],
            $validated['academic_term_id'],
            $validated['class_section_id'],
            $validated['student_ids'],
        );

        // Enrich each report
        $reports = $reports->map(function ($report) {
            $report['letter_grade'] = $this->normService->toLetterGrade($report['normalized_score']);
            $report['gpa'] = $this->normService->toGpa($report['normalized_score']);

            return $report;
        });

        // Class statistics
        $scores = $reports->pluck('normalized_score')->filter(fn ($s) => $s > 0);
        $stats = [
            'class_average' => round($scores->avg(), 2),
            'highest_score' => round($scores->max(), 2),
            'lowest_score' => round($scores->min(), 2),
            'total_students' => $reports->count(),
            'fully_graded' => $reports->where('is_complete', true)->count(),
        ];

        return response()->json([
            'reports' => $reports->values(),
            'statistics' => $stats,
        ]);
    }
}
