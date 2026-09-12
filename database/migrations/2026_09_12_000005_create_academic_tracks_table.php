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
        if (!Schema::hasTable('academic_tracks')) {
            Schema::create('academic_tracks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
                $table->foreignId('class_id')->constrained('institute_classes')->cascadeOnDelete();
                $table->string('track_name');
                $table->string('track_code')->nullable();
                $table->text('description')->nullable();
                $table->boolean('allow_custom_electives')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['institute_id', 'class_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_tracks');
    }
};
