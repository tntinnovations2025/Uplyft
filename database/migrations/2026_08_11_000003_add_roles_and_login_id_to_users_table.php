<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE: users (institute_id, role) and students (user_id, roll_number)
        // were already provisioned by 2026_08_11_000001_enhance_users_table_for_module2
        // and 2026_08_12_* student migrations. Only the teacher linkage columns
        // are added here to complete the teachers table.

        if (Schema::hasTable('teachers') && ! Schema::hasColumn('teachers', 'user_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        if (Schema::hasTable('teachers') && ! Schema::hasColumn('teachers', 'employee_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->string('employee_id')->unique()->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'employee_id']);
        });
    }
};
