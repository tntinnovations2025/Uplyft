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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            
            // Multi-Tenancy Tenant Isolation Foreign Key
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            
            // Developer A Academic Term domain reference
            $table->unsignedBigInteger('academic_term_id');
            
            // Student reference
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            
            // Attendance parameters
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'leave'])->default('present');
            
            $table->timestamps();

            // Compound unique constraint preventing duplicate daily logs per student per term
            $table->unique(['institute_id', 'academic_term_id', 'student_id', 'date'], 'unique_student_daily_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
