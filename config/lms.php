<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LMS Configuration — UPLYFT
    |--------------------------------------------------------------------------
    |
    | Centralized configuration for the Learning Management System,
    | RAG pipeline, embedding engine, and assessment subsystem.
    |
    */

    // ── Upload Limits ────────────────────────────────────────────────────────
    'max_upload_size_kb' => (int) env('LMS_MAX_UPLOAD_SIZE_KB', 524288), // 512 MB default

    // ── Embedding Engine ─────────────────────────────────────────────────────
    'embedding' => [
        'provider'  => env('EMBEDDING_PROVIDER', 'tfidf'),       // tfidf | openai | jina | huggingface
        'api_key'   => env('EMBEDDING_API_KEY', ''),
        'model'     => env('EMBEDDING_MODEL', 'text-embedding-3-small'),
        'dimension' => (int) env('EMBEDDING_DIMENSION', 256),
        'endpoint'  => env('EMBEDDING_ENDPOINT', 'https://api.openai.com/v1/embeddings'),
    ],

    // ── Vector Search ────────────────────────────────────────────────────────
    'vector_search' => [
        'top_k'              => (int) env('VECTOR_SEARCH_TOP_K', 20),
        'similarity_threshold' => (float) env('VECTOR_SIMILARITY_THRESHOLD', 0.15),
        'hybrid_fulltext_weight' => 0.4,   // Weight for FULLTEXT score in hybrid ranking
        'hybrid_vector_weight'   => 0.6,   // Weight for vector cosine score in hybrid ranking
    ],

    // ── Chunking Strategy ────────────────────────────────────────────────────
    'chunking' => [
        'max_tokens' => (int) env('CHUNK_MAX_TOKENS', 600),
        'overlap'    => (int) env('CHUNK_OVERLAP_TOKENS', 100),
        'min_chunk_length' => 20,  // Minimum word count to keep a chunk
    ],

    // ── Practice Test History ────────────────────────────────────────────────
    'practice_tests' => [
        'history_display_limit' => 10,  // Show last N attempts per student per subject
        'retain_all_records'    => true, // true = soft-archive, false = hard-delete old
        'max_attempts_per_subject' => env('LMS_MAX_PRACTICE_ATTEMPTS', 5), // Max practice attempts per student per subject
    ],

    // ── Document Processing ──────────────────────────────────────────────────
    'processing' => [
        'queue'             => env('DOCUMENT_PROCESSING_QUEUE', 'default'),
        'memory_limit'      => '1024M',
        'time_limit'        => 900, // seconds
    ],

];
