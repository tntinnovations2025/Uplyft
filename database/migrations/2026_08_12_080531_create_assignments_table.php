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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            
            $table->enum('type', ['assignment', 'quiz', 'exam'])->default('assignment');
            $table->string('title');
            $table->text('description_message')->nullable();
            $table->string('file_attachment_path')->nullable();
            $table->dateTime('deadline')->nullable();
            
            $table->boolean('is_ai_graded')->default(false);
            
            $table->timestamps();
            
            $table->index(['institute_id', 'teacher_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
