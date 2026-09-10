<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('assessments', 'target_type')) {
                $table->enum('target_type', ['section', 'multi_sections', 'specific_students'])->default('section')->after('class_section_id');
            }
            if (! Schema::hasColumn('assessments', 'target_section_ids')) {
                $table->json('target_section_ids')->nullable()->after('target_type');
            }
            if (! Schema::hasColumn('assessments', 'target_student_ids')) {
                $table->json('target_student_ids')->nullable()->after('target_section_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['target_type', 'target_section_ids', 'target_student_ids']);
        });
    }
};
