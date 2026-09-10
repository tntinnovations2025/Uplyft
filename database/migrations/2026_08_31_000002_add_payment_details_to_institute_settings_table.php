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
        if (Schema::hasTable('institute_settings')) {
            Schema::table('institute_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('institute_settings', 'show_payment_details')) {
                    $table->boolean('show_payment_details')->default(true)->after('currency_symbol');
                }
                if (!Schema::hasColumn('institute_settings', 'bank_name')) {
                    $table->string('bank_name')->nullable()->default('Habib Bank Limited (HBL)')->after('show_payment_details');
                }
                if (!Schema::hasColumn('institute_settings', 'bank_account_title')) {
                    $table->string('bank_account_title')->nullable()->default('Apex Educational Institute')->after('bank_name');
                }
                if (!Schema::hasColumn('institute_settings', 'bank_account_number')) {
                    $table->string('bank_account_number')->nullable()->after('bank_account_title');
                }
                if (!Schema::hasColumn('institute_settings', 'bank_iban')) {
                    $table->string('bank_iban')->nullable()->default('PK75 HABB 0001 2345 6789 0123')->after('bank_account_number');
                }
                if (!Schema::hasColumn('institute_settings', 'onebill_voucher_prefix')) {
                    $table->string('onebill_voucher_prefix')->nullable()->default('100')->after('bank_iban');
                }
                if (!Schema::hasColumn('institute_settings', 'payment_instructions')) {
                    $table->text('payment_instructions')->nullable()->after('onebill_voucher_prefix');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('institute_settings')) {
            Schema::table('institute_settings', function (Blueprint $table) {
                $columns = [
                    'show_payment_details',
                    'bank_name',
                    'bank_account_title',
                    'bank_account_number',
                    'bank_iban',
                    'onebill_voucher_prefix',
                    'payment_instructions',
                ];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('institute_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
