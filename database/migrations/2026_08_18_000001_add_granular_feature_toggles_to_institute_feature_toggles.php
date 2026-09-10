<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institute_feature_toggles', function (Blueprint $table) {
            if (!Schema::hasColumn('institute_feature_toggles', 'scholarships')) {
                $table->boolean('scholarships')->default(true);
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'master_directory')) {
                $table->boolean('master_directory')->default(true);
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'rooms_facilities')) {
                $table->boolean('rooms_facilities')->default(true);
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'classes_sections')) {
                $table->boolean('classes_sections')->default(true);
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'subjects_catalog')) {
                $table->boolean('subjects_catalog')->default(true);
            }
            if (!Schema::hasColumn('institute_feature_toggles', 'teacher_allocations')) {
                $table->boolean('teacher_allocations')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('institute_feature_toggles', function (Blueprint $table) {
            $table->dropColumn([
                'scholarships',
                'master_directory',
                'rooms_facilities',
                'classes_sections',
                'subjects_catalog',
                'teacher_allocations',
            ]);
        });
    }
};
