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
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (!Schema::hasColumn('students', 'academic_track_id')) {
                    $table->foreignId('academic_track_id')->nullable()->after('class_section_id')->constrained('academic_tracks')->nullOnDelete();
                }
                if (!Schema::hasColumn('students', 'selected_subject_ids')) {
                    $table->json('selected_subject_ids')->nullable()->after('academic_track_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'academic_track_id')) {
                    $table->dropForeign(['academic_track_id']);
                    $table->dropColumn('academic_track_id');
                }
                if (Schema::hasColumn('students', 'selected_subject_ids')) {
                    $table->dropColumn('selected_subject_ids');
                }
            });
        }
    }
};
