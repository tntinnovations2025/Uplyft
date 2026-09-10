<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'total_marks')) {
                $table->unsignedInteger('total_marks')->default(100)->after('credit_hours');
            }
            if (!Schema::hasColumn('subjects', 'passing_marks')) {
                $table->unsignedInteger('passing_marks')->default(33)->after('total_marks');
            }
            if (!Schema::hasColumn('subjects', 'mcq_weightage')) {
                $table->decimal('mcq_weightage', 5, 2)->default(20.00)->after('passing_marks');
            }
            if (!Schema::hasColumn('subjects', 'short_answer_weightage')) {
                $table->decimal('short_answer_weightage', 5, 2)->default(30.00)->after('mcq_weightage');
            }
            if (!Schema::hasColumn('subjects', 'long_answer_weightage')) {
                $table->decimal('long_answer_weightage', 5, 2)->default(30.00)->after('short_answer_weightage');
            }
            if (!Schema::hasColumn('subjects', 'assignment_quiz_weightage')) {
                $table->decimal('assignment_quiz_weightage', 5, 2)->default(20.00)->after('long_answer_weightage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn([
                'total_marks',
                'passing_marks',
                'mcq_weightage',
                'short_answer_weightage',
                'long_answer_weightage',
                'assignment_quiz_weightage',
            ]);
        });
    }
};
