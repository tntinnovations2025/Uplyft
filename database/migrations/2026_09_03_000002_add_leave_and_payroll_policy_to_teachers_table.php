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
            if (!Schema::hasColumn('teachers', 'allowed_absent_days_per_month')) {
                $table->integer('allowed_absent_days_per_month')->nullable()->default(2)->after('basic_salary_pkr');
            }
            if (!Schema::hasColumn('teachers', 'salary_disbursement_day')) {
                $table->integer('salary_disbursement_day')->nullable()->default(1)->after('allowed_absent_days_per_month');
            }
            if (!Schema::hasColumn('teachers', 'salary_deduction_type')) {
                $table->string('salary_deduction_type')->nullable()->default('pro_rata')->after('salary_disbursement_day');
            }
            if (!Schema::hasColumn('teachers', 'fixed_absent_deduction_amount')) {
                $table->decimal('fixed_absent_deduction_amount', 10, 2)->nullable()->default(0.00)->after('salary_deduction_type');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'basic_salary_pkr')) {
                $table->decimal('basic_salary_pkr', 10, 2)->nullable()->after('employment_type');
            }
            if (!Schema::hasColumn('users', 'allowed_absent_days_per_month')) {
                $table->integer('allowed_absent_days_per_month')->nullable()->default(2)->after('basic_salary_pkr');
            }
            if (!Schema::hasColumn('users', 'salary_disbursement_day')) {
                $table->integer('salary_disbursement_day')->nullable()->default(1)->after('allowed_absent_days_per_month');
            }
            if (!Schema::hasColumn('users', 'salary_deduction_type')) {
                $table->string('salary_deduction_type')->nullable()->default('pro_rata')->after('salary_disbursement_day');
            }
            if (!Schema::hasColumn('users', 'fixed_absent_deduction_amount')) {
                $table->decimal('fixed_absent_deduction_amount', 10, 2)->nullable()->default(0.00)->after('salary_deduction_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $cols = ['allowed_absent_days_per_month', 'salary_disbursement_day', 'salary_deduction_type', 'fixed_absent_deduction_amount'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('teachers', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = ['basic_salary_pkr', 'allowed_absent_days_per_month', 'salary_disbursement_day', 'salary_deduction_type', 'fixed_absent_deduction_amount'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
