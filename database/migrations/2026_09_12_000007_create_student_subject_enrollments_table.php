<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('student_subject_enrollments')) {
            Schema::create('student_subject_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('academic_track_id')->nullable()->constrained('academic_tracks')->nullOnDelete();
                $table->enum('enrollment_status', ['active', 'dropped', 'completed'])->default('active');
                $table->timestamps();

                $table->unique(['student_id', 'subject_id']);
                $table->index(['student_id', 'enrollment_status']);
                $table->index(['class_section_id', 'subject_id']);
                $table->index(['institute_id', 'enrollment_status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subject_enrollments');
    }
};
