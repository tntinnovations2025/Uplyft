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
        if (!Schema::hasColumn('academic_terms', 'promotion_deadline')) {
            Schema::table('academic_terms', function (Blueprint $table) {
                $table->dateTime('promotion_deadline')->nullable()->after('end_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('academic_terms', 'promotion_deadline')) {
            Schema::table('academic_terms', function (Blueprint $table) {
                $table->dropColumn('promotion_deadline');
            });
        }
    }
};
