<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'lecture_duration_minutes')) {
                $table->unsignedInteger('lecture_duration_minutes')->default(60)->after('credit_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'lecture_duration_minutes')) {
                $table->dropColumn('lecture_duration_minutes');
            }
        });
    }
};
