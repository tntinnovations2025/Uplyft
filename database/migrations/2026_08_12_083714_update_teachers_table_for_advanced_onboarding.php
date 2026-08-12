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
        Schema::table('teachers', function (Blueprint $table) {
            $table->integer('years_of_experience')->nullable();
            $table->string('specialization_subjects')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->decimal('basic_salary_pkr', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['years_of_experience', 'specialization_subjects', 'emergency_contact_phone', 'basic_salary_pkr']);
        });
    }
};
