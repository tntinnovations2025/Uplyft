<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->boolean('has_time_limit')->default(false)->after('end_time');
            $table->integer('duration_minutes')->nullable()->after('has_time_limit');
            $table->boolean('is_marksheet_saved')->default(false)->after('status');
            $table->timestamp('saved_at')->nullable()->after('is_marksheet_saved');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['has_time_limit', 'duration_minutes', 'is_marksheet_saved', 'saved_at']);
        });
    }
};
