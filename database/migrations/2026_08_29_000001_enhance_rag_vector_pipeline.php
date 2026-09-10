<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RAG Vector Pipeline Enhancement
     *
     * 1. Adds embedding_vector (JSON) + vector_dimension to rag_document_chunks.
     * 2. Adds question_hash to assessment_questions for deduplication.
     * 3. Adds ai_feedback to practice_test_sessions.
     * 4. Creates cosine_similarity() MySQL stored function.
     */
    public function up(): void
    {
        // ── 1. Enhance rag_document_chunks with vector storage ───────────
        if (Schema::hasTable('rag_document_chunks')) {
            Schema::table('rag_document_chunks', function (Blueprint $table) {
                if (!Schema::hasColumn('rag_document_chunks', 'embedding_vector')) {
                    $table->json('embedding_vector')->nullable()->after('content_hash');
                }
                if (!Schema::hasColumn('rag_document_chunks', 'vector_dimension')) {
                    $table->unsignedSmallInteger('vector_dimension')->default(0)->after('embedding_vector');
                }
                if (!Schema::hasColumn('rag_document_chunks', 'is_embedded')) {
                    $table->boolean('is_embedded')->default(false)->after('vector_dimension');
                }
            });
        }

        // ── 2. Add question_hash to assessment_questions for deduplication ──
        if (Schema::hasTable('assessment_questions')) {
            Schema::table('assessment_questions', function (Blueprint $table) {
                if (!Schema::hasColumn('assessment_questions', 'question_hash')) {
                    $table->string('question_hash', 64)->nullable()->after('chapter_reference');
                    $table->index('question_hash', 'idx_question_hash');
                }
            });
        }

        // ── 3. Add ai_feedback to practice_test_sessions ─────────────────
        if (Schema::hasTable('practice_test_sessions')) {
            Schema::table('practice_test_sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('practice_test_sessions', 'ai_feedback')) {
                    $table->text('ai_feedback')->nullable()->after('evaluation_results');
                }
            });
        }

        // ── 4. Create MySQL cosine_similarity() stored function ──────────
        // This function computes cosine similarity between two JSON float arrays
        // entirely within MySQL — no external vector DB required.
        DB::unprepared("
            DROP FUNCTION IF EXISTS cosine_similarity;
        ");

        DB::unprepared("
            CREATE FUNCTION cosine_similarity(vec_a JSON, vec_b JSON)
            RETURNS DOUBLE DETERMINISTIC
            BEGIN
                DECLARE i INT DEFAULT 0;
                DECLARE len INT;
                DECLARE dot_product DOUBLE DEFAULT 0.0;
                DECLARE norm_a DOUBLE DEFAULT 0.0;
                DECLARE norm_b DOUBLE DEFAULT 0.0;
                DECLARE val_a DOUBLE;
                DECLARE val_b DOUBLE;
                DECLARE magnitude DOUBLE;

                IF vec_a IS NULL OR vec_b IS NULL THEN
                    RETURN 0.0;
                END IF;

                SET len = JSON_LENGTH(vec_a);

                IF len = 0 OR len != JSON_LENGTH(vec_b) THEN
                    RETURN 0.0;
                END IF;

                WHILE i < len DO
                    SET val_a = CAST(JSON_EXTRACT(vec_a, CONCAT('\$[', i, ']')) AS DOUBLE);
                    SET val_b = CAST(JSON_EXTRACT(vec_b, CONCAT('\$[', i, ']')) AS DOUBLE);
                    SET dot_product = dot_product + (val_a * val_b);
                    SET norm_a = norm_a + (val_a * val_a);
                    SET norm_b = norm_b + (val_b * val_b);
                    SET i = i + 1;
                END WHILE;

                SET magnitude = SQRT(norm_a) * SQRT(norm_b);

                IF magnitude = 0.0 THEN
                    RETURN 0.0;
                END IF;

                RETURN dot_product / magnitude;
            END
        ");
    }

    public function down(): void
    {
        if (Schema::hasTable('rag_document_chunks')) {
            Schema::table('rag_document_chunks', function (Blueprint $table) {
                $table->dropColumn(['embedding_vector', 'vector_dimension', 'is_embedded']);
            });
        }

        if (Schema::hasTable('assessment_questions')) {
            Schema::table('assessment_questions', function (Blueprint $table) {
                $table->dropIndex('idx_question_hash');
                $table->dropColumn('question_hash');
            });
        }

        if (Schema::hasTable('practice_test_sessions')) {
            Schema::table('practice_test_sessions', function (Blueprint $table) {
                $table->dropColumn('ai_feedback');
            });
        }

        DB::unprepared("DROP FUNCTION IF EXISTS cosine_similarity;");
    }
};
