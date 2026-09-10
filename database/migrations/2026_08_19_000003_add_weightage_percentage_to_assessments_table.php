<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('assessments', 'weightage_percentage')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->decimal('weightage_percentage', 5, 2)->default(10.00)->after('total_marks');
                $table->boolean('is_paper_test')->default(false)->after('evaluation_mode');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('assessments', 'weightage_percentage')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropColumn(['weightage_percentage', 'is_paper_test']);
            });
        }
    }
};
