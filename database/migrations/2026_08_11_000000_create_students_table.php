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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy isolation
            $table->foreignId('institute_id')
                  ->constrained('institutes')
                  ->cascadeOnDelete();

            // Personal Details
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth');
            $table->string('blood_group', 5)->nullable();
            
            // Academic Profile
            $table->decimal('previous_marks', 5, 2); // Supporting e.g., 99.50% marks

            // Guardian Financial Profile for Tax Calculations
            $table->enum('guardian_tax_status', ['filer', 'non-filer'])->default('non-filer');

            $table->timestamps();

            // Indexing for faster multi-tenant querying
            $table->index(['institute_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
