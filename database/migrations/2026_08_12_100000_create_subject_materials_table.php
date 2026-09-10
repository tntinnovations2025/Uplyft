<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 6 — LMS: Subject Materials (PDF/book uploads per subject)
     */
    public function up(): void
    {
        Schema::create('subject_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('document_type')->default('textbook'); // textbook, notes, syllabus, reference
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->boolean('is_rag_indexed')->default(false);
            $table->timestamps();

            $table->index(['subject_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_materials');
    }
};
