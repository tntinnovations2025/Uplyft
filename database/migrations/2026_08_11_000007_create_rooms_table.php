<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained('institutes')->cascadeOnDelete();
            $table->string('room_number'); // e.g. Room 101, Lab 2, Hall A
            $table->string('building_block')->nullable(); // e.g. Science Block
            $table->unsignedInteger('capacity')->default(40);
            $table->timestamps();

            $table->index(['institute_id', 'room_number']);
        });

        Schema::table('class_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('class_sections', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('section_name')->constrained('rooms')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            if (Schema::hasColumn('class_sections', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }
        });

        Schema::dropIfExists('rooms');
    }
};
