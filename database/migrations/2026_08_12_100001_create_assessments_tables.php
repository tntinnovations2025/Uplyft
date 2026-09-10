<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 6 — Assessment Engine: Assessments & Questions
     */
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['midterm', 'final', 'quiz', 'assignment', 'project', 'homework', 'presentation']);
            $table->unsignedInteger('total_marks');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->dateTime('result_deadline')->nullable();
            $table->enum('evaluation_mode', ['manual', 'ai'])->default('manual');
            $table->enum('status', ['draft', 'published', 'in_progress', 'completed', 'graded'])->default('draft');
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->index(['subject_id', 'academic_term_id']);
            $table->index(['class_section_id', 'type']);
            $table->index('status');
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->enum('question_type', ['mcq', 'short', 'long']);
            $table->text('statement');
            $table->json('options')->nullable();           // JSON array for MCQ choices
            $table->text('correct_answer')->nullable();    // Correct answer / model answer
            $table->unsignedInteger('marks');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('chapter_reference')->nullable(); // e.g., "Chapter 3, Page 42"
            $table->timestamps();

            $table->index('assessment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }
};
