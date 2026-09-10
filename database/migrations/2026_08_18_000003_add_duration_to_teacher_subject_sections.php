<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('teacher_subject_sections', 'duration_minutes')) {
            Schema::table('teacher_subject_sections', function (Blueprint $table) {
                $table->unsignedInteger('duration_minutes')->default(60)->after('periods_per_week');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teacher_subject_sections', 'duration_minutes')) {
            Schema::table('teacher_subject_sections', function (Blueprint $table) {
                $table->dropColumn('duration_minutes');
            });
        }
    }
};
