<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Module 2 – Authentication, Security & Account Governance
     *
     * Adds:
     *  • identifier     – Custom login identifier (Roll Number / Employee ID)
     *  • role           – RBAC enum (global_admin, principal, teacher, student)
     *  • institute_id   – FK to institutes table (nullable for global_admin)
     *  • is_delegated_admin – Allows teachers to act on behalf of principals
     *  • email           – made nullable (some users may only have identifiers)
     *  • password_reset_notifications – Admin-mediated password reset workflow
     */
    public function up(): void
    {
        // ── Enhance users table ─────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            // Custom identifier: STU-2026/0101, EMP#402, FAC-MATH-01 etc.
            $table->string('identifier')->nullable()->unique()->after('name');

            // Role-based access control
            $table->enum('role', ['global_admin', 'principal', 'teacher', 'student'])
                  ->default('student')
                  ->after('identifier');

            // Tenant binding – nullable for global_admin
            $table->foreignId('institute_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('institutes')
                  ->nullOnDelete();

            // Delegation flag – only meaningful for teachers
            $table->boolean('is_delegated_admin')
                  ->default(false)
                  ->after('institute_id');

            // Track who created this account (audit trail)
            $table->foreignId('created_by')
                  ->nullable()
                  ->after('is_delegated_admin')
                  ->constrained('users')
                  ->nullOnDelete();

            // Soft deletes for account deactivation
            $table->softDeletes();

            // Composite index for fast institute-scoped queries
            $table->index(['institute_id', 'role']);
        });

        // Make email nullable since some users login with identifier only
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // ── Password Reset Notifications (Admin-Mediated Workflow) ──────────
        Schema::create('password_reset_notifications', function (Blueprint $table) {
            $table->id();

            // The user requesting the reset
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // The institute context
            $table->foreignId('institute_id')
                  ->nullable()
                  ->constrained('institutes')
                  ->nullOnDelete();

            // Status workflow: pending → approved → completed / denied
            $table->enum('status', ['pending', 'approved', 'completed', 'denied'])
                  ->default('pending');

            // Which admin role should handle this request
            // 'principal' for student/teacher resets, 'global_admin' for principal resets
            $table->enum('target_role', ['principal', 'global_admin']);

            // Optional: the admin who processed the request
            $table->foreignId('processed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'target_role']);
            $table->index(['institute_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_notifications');

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institute_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['institute_id', 'role']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'identifier',
                'role',
                'institute_id',
                'is_delegated_admin',
                'created_by',
            ]);
        });
    }
};
