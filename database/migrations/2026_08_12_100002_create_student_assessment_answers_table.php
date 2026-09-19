<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 6 — Dual-Evaluation: Student Assessment Answers
     */
    public function up(): void
    {
        if (! Schema::hasTable('student_assessment_answers')) {
            Schema::create('student_assessment_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_question_id')->constrained('assessment_questions')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->text('provided_answer')->nullable();
                $table->decimal('marks_awarded', 6, 2)->nullable();
                $table->text('ai_feedback')->nullable();
                $table->enum('grading_status', ['pending', 'auto_graded', 'ai_graded', 'manually_graded'])->default('pending');
                $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['assessment_question_id', 'student_id'], 'unique_student_question_answer');
                $table->index('student_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_assessment_answers');
    }
};
