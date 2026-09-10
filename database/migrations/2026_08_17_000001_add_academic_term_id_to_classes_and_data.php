<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add academic_term_id to institute_classes
        if (!Schema::hasColumn('institute_classes', 'academic_term_id')) {
            Schema::table('institute_classes', function (Blueprint $table) {
                $table->foreignId('academic_term_id')
                    ->nullable()
                    ->after('institute_id')
                    ->constrained('academic_terms')
                    ->nullOnDelete();
            });
        }

        // 2. Add academic_term_id to students
        if (!Schema::hasColumn('students', 'academic_term_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('academic_term_id')
                    ->nullable()
                    ->after('institute_id')
                    ->constrained('academic_terms')
                    ->nullOnDelete();
            });
        }

        // 3. Add academic_term_id to invoices
        if (!Schema::hasColumn('invoices', 'academic_term_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('academic_term_id')
                    ->nullable()
                    ->after('institute_id')
                    ->constrained('academic_terms')
                    ->nullOnDelete();
            });
        }

        // Backfill existing records to active or first academic_term per institute
        $institutes = DB::table('institutes')->get();
        foreach ($institutes as $inst) {
            $activeTerm = DB::table('academic_terms')
                ->where('institute_id', $inst->id)
                ->where('is_active', true)
                ->first()
                ?? DB::table('academic_terms')
                ->where('institute_id', $inst->id)
                ->first();

            if ($activeTerm) {
                DB::table('institute_classes')
                    ->where('institute_id', $inst->id)
                    ->whereNull('academic_term_id')
                    ->update(['academic_term_id' => $activeTerm->id]);

                DB::table('students')
                    ->where('institute_id', $inst->id)
                    ->whereNull('academic_term_id')
                    ->update(['academic_term_id' => $activeTerm->id]);

                DB::table('invoices')
                    ->where('institute_id', $inst->id)
                    ->whereNull('academic_term_id')
                    ->update(['academic_term_id' => $activeTerm->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'academic_term_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['academic_term_id']);
                $table->dropColumn('academic_term_id');
            });
        }

        if (Schema::hasColumn('students', 'academic_term_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['academic_term_id']);
                $table->dropColumn('academic_term_id');
            });
        }

        if (Schema::hasColumn('institute_classes', 'academic_term_id')) {
            Schema::table('institute_classes', function (Blueprint $table) {
                $table->dropForeign(['academic_term_id']);
                $table->dropColumn('academic_term_id');
            });
        }
    }
};
