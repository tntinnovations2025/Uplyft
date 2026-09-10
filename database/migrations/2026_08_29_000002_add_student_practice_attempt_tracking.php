<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Student Practice Attempt Tracking
     *
     * Dedicated table to track the last N practice test attempts per student per subject,
     * with score breakdowns, AI feedback, and attempt numbering.
     */
    public function up(): void
    {
        Schema::create('student_practice_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->uuid('practice_test_session_id');
            $table->foreign('practice_test_session_id')
                  ->references('id')
                  ->on('practice_test_sessions')
                  ->cascadeOnDelete();

            $table->unsignedInteger('attempt_number')->default(1);
            $table->decimal('score_percentage', 5, 2)->default(0.00);
            $table->decimal('total_marks', 8, 2)->default(0.00);
            $table->decimal('obtained_marks', 8, 2)->default(0.00);

            $table->unsignedSmallInteger('mcq_correct')->default(0);
            $table->unsignedSmallInteger('mcq_total')->default(0);
            $table->unsignedSmallInteger('short_answered')->default(0);
            $table->unsignedSmallInteger('short_total')->default(0);
            $table->unsignedSmallInteger('long_answered')->default(0);
            $table->unsignedSmallInteger('long_total')->default(0);

            $table->json('question_breakdown')->nullable();
            $table->text('ai_summary_feedback')->nullable();
            $table->string('score_grade', 2)->nullable(); // A+, A, B, C, D, F

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Composite indexes for fast lookups
            $table->index(['user_id', 'subject_id', 'attempt_number'], 'idx_user_subject_attempt');
            $table->index(['user_id', 'subject_id', 'completed_at'], 'idx_user_subject_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_practice_attempts');
    }
};
