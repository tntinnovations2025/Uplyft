<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── system_classes ──────────────────────────────────────────────────
        // Stores the master list of all class/program definitions.
        // Created exclusively by the Global Admin.
        // Examples: "Play Group", "Grade 1", "Class 10", "1st Year",
        //           "ACCA - FA1", "O Level - AS", "ACCA - P7"
        Schema::create('system_classes', function (Blueprint $table) {
            $table->id();

            $table->string('name');                               // e.g. "Grade 10", "ACCA - FA1"
            $table->string('short_code')->unique();              // e.g. "G10", "ACCA-FA1"

            $table->enum('education_type', [
                'matric',        // Play Group → Grade 10
                'higher_sec',    // 1st Year, 2nd Year (FSc / FA / ICS)
                'o_a_level',     // O/A Level programs
                'professional',  // ACCA, CA, CMA, etc.
                'other',
            ])->default('matric');

            $table->tinyInteger('sort_order')->default(0);        // Controls display ordering
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('education_type');
            $table->index('is_active');
        });

        // ── institute_class_assignments ──────────────────────────────────────
        // Pivot table: which global classes are assigned to which institutes
        // (controlled by subscription tier and Global Admin decision)
        Schema::create('institute_class_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institute_id')
                  ->constrained('institutes')
                  ->onDelete('cascade');

            $table->foreignId('system_class_id')
                  ->constrained('system_classes')
                  ->onDelete('cascade');

            $table->boolean('is_active')->default(true);         // Can be individually disabled
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['institute_id', 'system_class_id']); // No duplicate assignments
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_class_assignments');
        Schema::dropIfExists('system_classes');
    }
};
