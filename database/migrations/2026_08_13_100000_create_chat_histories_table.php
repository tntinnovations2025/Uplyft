<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('session_id', 64)->index();
            $table->enum('role', ['user', 'assistant'])->default('user');
            $table->text('message');
            $table->json('sources')->nullable();
            $table->float('confidence')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'subject_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_histories');
    }
};
