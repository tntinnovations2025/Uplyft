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
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('qualifications_file_path');
            $table->string('matriculation_cert');
            $table->string('intermediate_cert');
            $table->string('bachelors_cert');
            $table->string('masters_cert')->nullable();
            $table->string('phd_cert')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('qualifications_file_path')->nullable();
            $table->dropColumn(['matriculation_cert', 'intermediate_cert', 'bachelors_cert', 'masters_cert', 'phd_cert']);
        });
    }
};
