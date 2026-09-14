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
        // 1. Update assessments table for scheduling & teacher/class assignments
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'teacher_id')) {
                $table->foreignId('teacher_id')
                    ->nullable()
                    ->after('creator_id')
                    ->constrained('users')
                    ->onDelete('cascade');
            }

            if (!Schema::hasColumn('assessments', 'institute_class_id')) {
                $table->foreignId('institute_class_id')
                    ->nullable()
                    ->after('academic_term_id')
                    ->constrained('institute_classes')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('assessments', 'total_questions')) {
                $table->integer('total_questions')->nullable()->after('total_mcqs');
            }

            if (!Schema::hasColumn('assessments', 'scheduled_date')) {
                $table->date('scheduled_date')->nullable()->after('duration_minutes');
            }

            if (!Schema::hasColumn('assessments', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('status');
            }
        });

        // 2. Create assessment_submissions table
        if (!Schema::hasTable('assessment_submissions')) {
            Schema::create('assessment_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')
                    ->constrained('assessments')
                    ->onDelete('cascade');
                $table->foreignId('student_id')
                    ->constrained('users')
                    ->onDelete('cascade');
                $table->timestamp('started_at');
                $table->timestamp('submitted_at')->nullable();
                $table->json('answers')->nullable();
                $table->decimal('total_score', 5, 2)->default(0.00);
                $table->enum('status', ['in_progress', 'completed', 'auto_submitted'])->default('in_progress');
                $table->timestamps();

                $table->index(['assessment_id', 'student_id']);
                $table->index(['student_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_submissions');

        Schema::table('assessments', function (Blueprint $table) {
            $colsToDrop = [];
            foreach (['teacher_id', 'institute_class_id', 'total_questions', 'scheduled_date', 'is_published'] as $col) {
                if (Schema::hasColumn('assessments', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
