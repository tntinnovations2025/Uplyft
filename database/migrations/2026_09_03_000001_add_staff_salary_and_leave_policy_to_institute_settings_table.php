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
        Schema::table('institute_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('institute_settings', 'monthly_staff_salary_budget')) {
                $table->decimal('monthly_staff_salary_budget', 12, 2)->default(0.00)->after('monthly_expense_budget');
            }
            if (!Schema::hasColumn('institute_settings', 'salary_disbursement_day')) {
                $table->integer('salary_disbursement_day')->default(1)->after('monthly_staff_salary_budget');
            }
            if (!Schema::hasColumn('institute_settings', 'allowed_absent_days_per_month')) {
                $table->integer('allowed_absent_days_per_month')->default(2)->after('salary_disbursement_day');
            }
            if (!Schema::hasColumn('institute_settings', 'faculty_off_days')) {
                $table->string('faculty_off_days')->default('Sunday')->after('allowed_absent_days_per_month');
            }
            if (!Schema::hasColumn('institute_settings', 'salary_deduction_type')) {
                $table->string('salary_deduction_type')->default('pro_rata')->after('faculty_off_days');
            }
            if (!Schema::hasColumn('institute_settings', 'fixed_absent_deduction_amount')) {
                $table->decimal('fixed_absent_deduction_amount', 10, 2)->default(0.00)->after('salary_deduction_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institute_settings', function (Blueprint $table) {
            $columns = [
                'monthly_staff_salary_budget',
                'salary_disbursement_day',
                'allowed_absent_days_per_month',
                'faculty_off_days',
                'salary_deduction_type',
                'fixed_absent_deduction_amount',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('institute_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
