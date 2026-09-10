<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'staff_role')) {
                $table->string('staff_role')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'employment_type')) {
                $table->enum('employment_type', ['permanent', 'contractual'])->nullable()->after('staff_role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'staff_role')) {
                $table->dropColumn('staff_role');
            }
            if (Schema::hasColumn('users', 'employment_type')) {
                $table->dropColumn('employment_type');
            }
        });
    }
};
