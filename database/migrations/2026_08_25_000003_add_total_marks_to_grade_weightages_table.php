<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_weightages', function (Blueprint $table) {
            if (!Schema::hasColumn('grade_weightages', 'total_marks')) {
                $table->unsignedInteger('total_marks')->default(100)->after('assessment_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grade_weightages', function (Blueprint $table) {
            $table->dropColumn('total_marks');
        });
    }
};
