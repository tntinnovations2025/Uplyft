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
                if (!Schema::hasColumn('institute_settings', 'filer_tax_rate')) {
                    $table->decimal('filer_tax_rate', 6, 4)->default(0.0500)->after('late_fee_fine_amount');
                }
                if (!Schema::hasColumn('institute_settings', 'non_filer_tax_rate')) {
                    $table->decimal('non_filer_tax_rate', 6, 4)->default(0.1500)->after('filer_tax_rate');
                }
                if (!Schema::hasColumn('institute_settings', 'base_admission_fee')) {
                    $table->decimal('base_admission_fee', 12, 2)->default(10000.00)->after('non_filer_tax_rate');
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
                $columns = ['filer_tax_rate', 'non_filer_tax_rate', 'base_admission_fee'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('institute_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
