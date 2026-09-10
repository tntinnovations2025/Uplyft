<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds guardian_phone and class_section_id fields to students table.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'guardian_phone')) {
                $table->string('guardian_phone')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('students', 'class_section_id')) {
                $table->foreignId('class_section_id')
                    ->nullable()
                    ->after('enrolled_program')
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
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'class_section_id')) {
                $table->dropForeign(['class_section_id']);
                $table->dropColumn('class_section_id');
            }
            if (Schema::hasColumn('students', 'guardian_phone')) {
                $table->dropColumn('guardian_phone');
            }
        });
    }
};
