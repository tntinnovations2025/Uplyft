<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_test_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('title');
            $table->integer('mcq_count')->default(0);
            $table->integer('short_count')->default(0);
            $table->integer('long_count')->default(0);
            $table->integer('total_marks')->default(0);
            $table->float('obtained_marks')->nullable();
            $table->json('questions');
            $table->json('student_answers')->nullable();
            $table->json('evaluation_results')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->timestamp('scheduled_start_at')->nullable();
            $table->timestamp('scheduled_end_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'evaluated', 'expired'])->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_test_sessions');
    }
};
