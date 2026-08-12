<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Teacher Availabilities Table
        Schema::create('teacher_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('15:00:00');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['teacher_id', 'day_of_week']);
        });

        // 2. Course Allocations Table (Teaching Requirements to auto-schedule)
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

    public function down(): void
    {
        Schema::dropIfExists('course_allocations');
        Schema::dropIfExists('teacher_availabilities');
    }
};
