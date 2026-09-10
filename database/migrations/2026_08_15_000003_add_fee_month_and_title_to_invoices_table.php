<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add fee_month, title, and class_section_id to invoices table.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'title')) {
                $table->string('title')->nullable()->after('student_id');
            }
            if (! Schema::hasColumn('invoices', 'fee_month')) {
                $table->string('fee_month')->nullable()->after('title');
            }
            if (! Schema::hasColumn('invoices', 'class_section_id')) {
                $table->foreignId('class_section_id')
                    ->nullable()
                    ->after('fee_month')
                    ->constrained('class_sections')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'class_section_id')) {
                $table->dropForeign(['class_section_id']);
                $table->dropColumn('class_section_id');
            }
            if (Schema::hasColumn('invoices', 'fee_month')) {
                $table->dropColumn('fee_month');
            }
            if (Schema::hasColumn('invoices', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
