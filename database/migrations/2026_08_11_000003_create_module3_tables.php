<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Module 3:
     *  1. academic_terms
     *  2. institute_classes
     *  3. subjects
     *  4. class_sections
     *  5. timetables
     *  6. teacher_subject_sections
     */
    public function up(): void
    {
        // 1. Academic Terms
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->string('name'); // e.g. 2025-2026, Fall 2026
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institute_id', 'is_active']);
        });

        // 2. Institute Classes (Provisioned levels / professional modules per institute)
        Schema::create('institute_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->foreignId('system_class_id')->nullable()->constrained('system_classes')->nullOnDelete();
            $table->string('custom_name'); // e.g. "Grade 10", "1st Year", "FA1 - Financial Accounting"
            $table->timestamps();

            $table->index(['institute_id', 'custom_name']);
        });

        // 3. Subjects (Custom subjects attached to an institute class)
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_class_id')->constrained('institute_classes')->cascadeOnDelete();
            $table->string('subject_name'); // e.g. "Physics", "Financial Accounting (FA1)"
            $table->string('subject_code')->nullable(); // e.g. "PHY-101", "FA1"
            $table->unsignedInteger('credit_hours')->default(3);
            $table->timestamps();

            $table->index(['institute_class_id', 'subject_code']);
        });

        // 4. Class Sections (Subsections of a class, e.g., 10-A, FA1-Section 1)
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_class_id')->constrained('institute_classes')->cascadeOnDelete();
            $table->string('section_name'); // e.g. "Section A", "Sec 1"
            $table->unsignedInteger('capacity')->default(40);
            $table->timestamps();

            $table->index(['institute_class_id', 'section_name']);
        });

        // 5. Timetables (Conflict-free matrix engine)
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            // Indexes for fast conflict querying
            $table->index(['academic_term_id', 'teacher_id', 'day_of_week']);
            $table->index(['academic_term_id', 'class_section_id', 'day_of_week']);
        });

        // 6. Teacher Subject Section Pivot
        Schema::create('teacher_subject_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['academic_term_id', 'subject_id', 'class_section_id'], 'unique_subject_section_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subject_sections');
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('class_sections');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('institute_classes');
        Schema::dropIfExists('academic_terms');
    }
};
