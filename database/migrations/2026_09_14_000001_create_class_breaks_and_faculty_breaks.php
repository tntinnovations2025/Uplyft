<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add break columns to teacher_availabilities
        Schema::table('teacher_availabilities', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_availabilities', 'break_start_time')) {
                $table->time('break_start_time')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('teacher_availabilities', 'break_end_time')) {
                $table->time('break_end_time')->nullable()->after('break_start_time');
            }
        });

        // 2. Create class_breaks table for Principal Class Break settings
        if (!Schema::hasTable('class_breaks')) {
            Schema::create('class_breaks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
                $table->foreignId('academic_term_id')->nullable()->constrained('academic_terms')->cascadeOnDelete();
                $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
                $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
                $table->time('break_start_time');
                $table->time('break_end_time');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['institute_id', 'class_section_id', 'day_of_week']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_breaks');

        Schema::table('teacher_availabilities', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_availabilities', 'break_end_time')) {
                $table->dropColumn('break_end_time');
            }
            if (Schema::hasColumn('teacher_availabilities', 'break_start_time')) {
                $table->dropColumn('break_start_time');
            }
        });
    }
};
