<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for O/A Levels Mock Examination Generation Engine.
     */
    public function up(): void
    {
        // 1. Table: past_paper_uploads
        if (!Schema::hasTable('past_paper_uploads')) {
            Schema::create('past_paper_uploads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->string('exam_series'); // e.g. "May/June 2024 - Variant 12"
                $table->string('file_path');
                $table->enum('parsed_status', ['pending', 'processing', 'indexed', 'failed'])->default('pending');
                $table->unsignedInteger('total_questions_extracted')->default(0);
                $table->timestamps();

                $table->index(['institute_id', 'subject_id']);
                $table->index('parsed_status');
            });
        }

        // 2. Table: past_paper_exemplars (Few-Shot Style Bank)
        if (!Schema::hasTable('past_paper_exemplars')) {
            Schema::create('past_paper_exemplars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('past_paper_upload_id')->constrained('past_paper_uploads')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->string('topic_tag')->nullable();
                $table->text('question_stem');
                $table->json('options'); // JSON structure with {A: "...", B: "...", C: "...", D: "..."}
                $table->string('correct_answer', 10); // "A", "B", "C", "D"
                $table->text('explanation')->nullable();
                $table->string('stem_hash', 64)->index();
                $table->json('embedding')->nullable();
                $table->timestamps();

                $table->index(['subject_id', 'stem_hash']);
            });
        }

        // 3. Add Mock Examination fields to assessments table
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'is_mock')) {
                $table->boolean('is_mock')->default(false)->after('status');
                $table->index('is_mock');
            }
            if (!Schema::hasColumn('assessments', 'exam_standard')) {
                $table->enum('exam_standard', ['standard', 'caie_o_level', 'caie_a_level', 'edexcel'])->nullable()->after('is_mock');
            }
            if (!Schema::hasColumn('assessments', 'total_mcqs')) {
                $table->unsignedInteger('total_mcqs')->default(40)->after('exam_standard');
            }
        });

        // 4. Add embedding column to assessment_questions table if not present for semantic deduplication
        Schema::table('assessment_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('assessment_questions', 'embedding')) {
                $table->json('embedding')->nullable()->after('question_hash');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_questions', 'embedding')) {
                $table->dropColumn('embedding');
            }
        });

        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'is_mock')) {
                $table->dropIndex(['is_mock']);
                $table->dropColumn(['is_mock', 'exam_standard', 'total_mcqs']);
            }
        });

        Schema::dropIfExists('past_paper_exemplars');
        Schema::dropIfExists('past_paper_uploads');
    }
};
