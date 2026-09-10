<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // No-op. Superseded by 2026_08_10_000001_create_institutes_table,
        // which owns the canonical institutes schema. This file is kept only
        // for migration-record compatibility on already-installed databases.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutes');
    }
};
