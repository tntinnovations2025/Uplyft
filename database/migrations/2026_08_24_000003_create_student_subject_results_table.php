<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Subject-level result tracking for granular promotion decisions.
     * Tracks which specific subjects a student passed or failed.
     */
    public function up(): void
    {
        if (!Schema::hasTable('student_subject_results')) {
            Schema::create('student_subject_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
                $table->unsignedBigInteger('institute_id');
                $table->unsignedInteger('marks_obtained')->default(0);
                $table->unsignedInteger('total_marks')->default(100);
                $table->enum('result_status', ['passed', 'failed', 'pending'])->default('pending');
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->unique(
                    ['student_id', 'subject_id', 'academic_term_id'],
                    'unique_student_subject_term_result'
                );
                $table->index(['institute_id', 'academic_term_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_results');
    }
};
