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
        if (!Schema::hasTable('daily_diaries')) {
            Schema::create('daily_diaries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
                $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->enum('entry_type', ['homework', 'test_alert', 'announcement', 'classwork'])->default('homework');
                $table->string('title');
                $table->text('content');
                $table->date('assigned_date');
                $table->dateTime('expires_at');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['institute_id', 'assigned_date']);
                $table->index(['class_section_id', 'subject_id']);
                $table->index(['teacher_id', 'assigned_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_diaries');
    }
};
