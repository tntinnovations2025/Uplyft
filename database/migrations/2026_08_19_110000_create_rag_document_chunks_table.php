<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Module 6 — LMS: RAG Vector Document Chunks
     *
     * Stores chunked text content extracted from uploaded PDF/Word documents.
     * Each chunk is a segment of a document used for RAG retrieval.
     * The content_hash column enables fast similarity/keyword matching in MySQL.
     */
    public function up(): void
    {
        Schema::create('rag_document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_material_id')->constrained('subject_materials')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index')->default(0);          // position within document
            $table->longText('chunk_content');                            // the actual text chunk
            $table->unsignedInteger('token_count')->default(0);          // approx token count
            $table->string('content_hash', 64)->nullable()->index();     // SHA-256 for deduplication
            $table->timestamps();

            $table->index(['subject_id', 'subject_material_id']);
            $table->index(['subject_id', 'chunk_index']);

            // Full-text index for keyword-based RAG retrieval
            $table->fullText('chunk_content', 'ft_chunk_content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rag_document_chunks');
    }
};
