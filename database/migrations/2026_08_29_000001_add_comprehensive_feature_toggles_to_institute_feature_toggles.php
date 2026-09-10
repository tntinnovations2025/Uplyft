<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institute_feature_toggles', function (Blueprint $table) {
            if (!Schema::hasColumn('institute_feature_toggles', 'faculty_hours')) {
                $table->boolean('faculty_hours')->default(true)->after('teacher_allocations');
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'staff_governance')) {
                $table->boolean('staff_governance')->default(true)->after('faculty_hours');
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'financial_accounts')) {
                $table->boolean('financial_accounts')->default(true)->after('staff_governance');
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'security_management')) {
                $table->boolean('security_management')->default(true)->after('financial_accounts');
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'datesheet_manager')) {
                $table->boolean('datesheet_manager')->default(true)->after('security_management');
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'exam_reports')) {
                $table->boolean('exam_reports')->default(true)->after('datesheet_manager');
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'practice_tests')) {
                $table->boolean('practice_tests')->default(true)->after('exam_reports');
            }
        });
    }

    public function down(): void
    {
        Schema::table('institute_feature_toggles', function (Blueprint $table) {
            $cols = [
                'faculty_hours',
                'staff_governance',
                'financial_accounts',
                'security_management',
                'datesheet_manager',
                'exam_reports',
                'practice_tests',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('institute_feature_toggles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
