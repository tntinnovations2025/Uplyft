<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drops global unique constraint on teachers.employee_id so employee IDs
     * can collide across different tenant institutes.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique('teachers_employee_id_unique');
            $table->unique(['institute_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['institute_id', 'employee_id']);
            $table->unique('employee_id', 'teachers_employee_id_unique');
        });
    }
};
