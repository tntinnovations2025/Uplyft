<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 6 — Grade Normalization: Weightage Configuration
     */
    public function up(): void
    {
        Schema::create('grade_weightages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->foreignId('configured_by')->constrained('users')->cascadeOnDelete();
            $table->string('assessment_type'); // midterm, final, quiz, assignment, project, homework, presentation
            $table->decimal('weightage_percentage', 5, 2);
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();

            $table->unique(
                ['subject_id', 'academic_term_id', 'class_section_id', 'assessment_type'],
                'unique_grade_weightage'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_weightages');
    }
};
