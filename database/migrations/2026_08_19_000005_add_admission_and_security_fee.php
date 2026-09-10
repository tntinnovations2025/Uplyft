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
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('admission_fee', 10, 2)->default(0.00)->after('base_fee');
            $table->decimal('security_fee', 10, 2)->default(0.00)->after('admission_fee');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('admission_fee', 10, 2)->default(0.00)->after('amount_pkr');
            $table->decimal('security_fee', 10, 2)->default(0.00)->after('admission_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['admission_fee', 'security_fee']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['admission_fee', 'security_fee']);
        });
    }
};
