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
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('room')->nullable()->after('instructions');
            $table->boolean('is_published_teacher')->default(true)->after('room');
            $table->boolean('is_published_student')->default(true)->after('is_published_teacher');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['room', 'is_published_teacher', 'is_published_student']);
        });
    }
};
