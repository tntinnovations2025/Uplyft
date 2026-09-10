<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add page_number, chapter, topic, and book_portion metadata columns
     * to rag_document_chunks table for granular topic/page/chapter test generation.
     */
    public function up(): void
    {
        Schema::table('rag_document_chunks', function (Blueprint $table) {
            $table->unsignedInteger('page_number')->nullable()->after('chunk_index')->index();
            $table->unsignedInteger('chapter_number')->nullable()->after('page_number')->index();
            $table->string('chapter_title', 255)->nullable()->after('chapter_number')->index();
            $table->string('topic_title', 255)->nullable()->after('chapter_title')->index();
            $table->string('book_portion', 50)->default('complete')->after('topic_title')->index(); // 'first_half', 'second_half', 'complete'
        });
    }

    public function down(): void
    {
        Schema::table('rag_document_chunks', function (Blueprint $table) {
            $table->dropColumn(['page_number', 'chapter_number', 'chapter_title', 'topic_title', 'book_portion']);
        });
    }
};
