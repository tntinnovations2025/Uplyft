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
        if (!Schema::hasTable('institute_settings')) {
            Schema::create('institute_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('institute_id')->unique();
                
                // Attendance Architecture & Policies
                $table->string('attendance_mode')->default('subject'); // 'subject' or 'daily'
                $table->decimal('min_required_attendance_pct', 5, 2)->default(75.00);
                $table->string('attendance_start_time')->default('08:00');
                $table->string('attendance_end_time')->default('16:00');
                $table->boolean('allow_past_attendance_edits')->default(false);
                $table->boolean('is_attendance_locked_override')->default(false);

                // Financial Targets & Budgets
                $table->decimal('monthly_income_target', 12, 2)->default(350000.00);
                $table->decimal('monthly_expense_budget', 12, 2)->default(200000.00);
                $table->integer('fee_due_day_of_month')->default(10);
                $table->decimal('late_fee_fine_amount', 10, 2)->default(500.00);
                $table->string('currency_symbol')->default('PKR');

                // Academic & Passing Thresholds
                $table->decimal('passing_percentage', 5, 2)->default(40.00);
                $table->string('grade_scale_type')->default('percentage');

                $table->timestamps();

                $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institute_settings');
    }
};
