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
        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('b_form_or_father_cnic', 'student_bform_cnic');
            $table->renameColumn('guardian_cnic', 'father_guardian_cnic');
            $table->renameColumn('enrolled_class', 'enrolled_program');
            $table->string('father_guardian_name')->nullable()->after('last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('student_bform_cnic', 'b_form_or_father_cnic');
            $table->renameColumn('father_guardian_cnic', 'guardian_cnic');
            $table->renameColumn('enrolled_program', 'enrolled_class');
            $table->dropColumn('father_guardian_name');
        });
    }
};
