<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Course allocations are replaced by the TeacherSubjectSection flow
        // (teacher → subject → class section) as the single source of truth
        // for the optimistic timetable generator.
        Schema::dropIfExists('course_allocations');

        // Each teacher subject section assignment now carries how many
        // weekly periods it requires from the teacher's availability window.
        Schema::table('teacher_subject_sections', function (Blueprint $table) {
            $table->unsignedInteger('periods_per_week')->default(3)->after('class_section_id');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_subject_sections', function (Blueprint $table) {
            $table->dropColumn('periods_per_week');
        });

        Schema::create('course_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('preferred_room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->integer('periods_per_week')->default(3);
            $table->integer('duration_minutes')->default(60);
            $table->timestamps();
        });
    }
};
