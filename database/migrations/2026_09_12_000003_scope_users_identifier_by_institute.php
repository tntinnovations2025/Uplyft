<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drops global unique constraint on users.identifier so roll numbers / employee IDs
     * are scoped per tenant (BUG-AUTH-001 & BUG-AUTH-002).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop global unique index on identifier
            $table->dropUnique('users_identifier_unique');
            // Add composite index for tenant-scoped lookups
            $table->index(['institute_id', 'identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['institute_id', 'identifier']);
            $table->unique('identifier', 'users_identifier_unique');
        });
    }
};
