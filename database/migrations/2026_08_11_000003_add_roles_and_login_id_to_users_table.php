<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add role + login_id + institute_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('institute_id')->nullable()->after('id');
            $table->string('login_id')->unique()->nullable()->after('name');
            $table->enum('role', ['admin', 'teacher', 'student'])->default('student')->after('login_id');
        });

        // 2. Add user_id + roll_number to students table
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('id');
            $table->string('roll_number')->unique()->nullable()->after('user_id');
        });

        // 3. Add user_id + employee_id to teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('id');
            $table->string('employee_id')->unique()->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'employee_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'roll_number']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['login_id']);
            $table->dropColumn(['institute_id', 'login_id', 'role']);
        });
    }
};
