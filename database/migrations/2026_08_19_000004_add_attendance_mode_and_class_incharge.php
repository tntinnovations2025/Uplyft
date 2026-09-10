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
        if (Schema::hasTable('attendance_settings')) {
            Schema::table('attendance_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_settings', 'attendance_mode')) {
                    $table->string('attendance_mode')->default('subject')->after('institute_id');
                }
            });
        }

        if (Schema::hasTable('class_sections')) {
            Schema::table('class_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('class_sections', 'class_incharge_id')) {
                    $table->unsignedBigInteger('class_incharge_id')->nullable()->after('room_id');
                    $table->foreign('class_incharge_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'subject_id')) {
                    $table->unsignedBigInteger('subject_id')->nullable()->after('student_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendance_settings')) {
            Schema::table('attendance_settings', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_settings', 'attendance_mode')) {
                    $table->dropColumn('attendance_mode');
                }
            });
        }

        if (Schema::hasTable('class_sections')) {
            Schema::table('class_sections', function (Blueprint $table) {
                if (Schema::hasColumn('class_sections', 'class_incharge_id')) {
                    $table->dropForeign(['class_incharge_id']);
                    $table->dropColumn('class_incharge_id');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'subject_id')) {
                    $table->dropColumn('subject_id');
                }
            });
        }
    }
};
