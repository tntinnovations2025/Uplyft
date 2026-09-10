<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'show_marks_to_student')) {
                $table->boolean('show_marks_to_student')->default(true);
            }
            if (!Schema::hasColumn('subjects', 'show_grade_to_student')) {
                $table->boolean('show_grade_to_student')->default(true);
            }
            if (!Schema::hasColumn('subjects', 'grade_scale_json')) {
                $table->text('grade_scale_json')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['show_marks_to_student', 'show_grade_to_student', 'grade_scale_json']);
        });
    }
};
