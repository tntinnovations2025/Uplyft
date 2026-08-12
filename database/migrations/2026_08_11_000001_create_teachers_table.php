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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            
            // Multi-Tenancy Tenant Isolation Foreign Key
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            
            // Personal Details & Academic Transcripts Path
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('qualification');
            $table->string('qualifications_file_path')->nullable();
            
            $table->timestamps();

            // Compound index for scoped lookups
            $table->index(['institute_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
