<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('teacher_subject_sections', 'allowed_days')) {
            Schema::table('teacher_subject_sections', function (Blueprint $table) {
                $table->json('allowed_days')->nullable()->after('duration_minutes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teacher_subject_sections', 'allowed_days')) {
            Schema::table('teacher_subject_sections', function (Blueprint $table) {
                $table->dropColumn('allowed_days');
            });
        }
    }
};
