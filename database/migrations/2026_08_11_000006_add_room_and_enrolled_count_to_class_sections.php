<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            if (!Schema::hasColumn('class_sections', 'room_number')) {
                $table->string('room_number')->nullable()->after('section_name');
            }
            if (!Schema::hasColumn('class_sections', 'enrolled_students')) {
                $table->unsignedInteger('enrolled_students')->default(0)->after('capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            if (Schema::hasColumn('class_sections', 'room_number')) {
                $table->dropColumn('room_number');
            }
            if (Schema::hasColumn('class_sections', 'enrolled_students')) {
                $table->dropColumn('enrolled_students');
            }
        });
    }
};
