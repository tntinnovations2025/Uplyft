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
        Schema::create('role_default_authorities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained('institutes')->onDelete('cascade');
            $table->string('role_slug');
            $table->string('role_name');
            $table->json('permissions')->nullable();
            $table->boolean('is_custom')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['institute_id', 'role_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_default_authorities');
    }
};
