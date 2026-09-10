<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institute_feature_toggles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')
                  ->constrained('institutes')
                  ->onDelete('cascade');   // If institute deleted, toggles go too

            // ── Core Feature Flags ──────────────────────────────────────────
            // Each column represents one platform module that Global Admin
            // can independently enable or disable per institute / plan tier.

            $table->boolean('ai_bot')->default(false);               // AI/RAG LMS Bot
            $table->boolean('attendance_system')->default(false);     // Student & Teacher Attendance
            $table->boolean('assessment_engine')->default(false);     // MCQ/Short/Long exam system
            $table->boolean('fee_invoicing')->default(false);         // Fee slips + tax calculations
            $table->boolean('timetable')->default(false);            // Class timetable builder
            $table->boolean('registration_portals')->default(false);  // Admissions & enrollment forms
            $table->boolean('lms_content')->default(false);          // PDF textbook + chapter uploads
            $table->boolean('grading_normalizer')->default(false);   // End-of-year grade weightage
            $table->boolean('teacher_portal')->default(false);       // Teacher credential + portal
            $table->boolean('principal_portal')->default(false);     // Principal governance panel
            $table->boolean('parent_portal')->default(false);        // Parent visibility (future)
            $table->boolean('sms_notifications')->default(false);    // SMS gateway integration

            // Override: last editor identity for audit trail
            $table->foreignId('last_updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('last_updated_at')->nullable();

            $table->timestamps();

            $table->unique('institute_id');   // One toggle row per institute
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_feature_toggles');
    }
};
