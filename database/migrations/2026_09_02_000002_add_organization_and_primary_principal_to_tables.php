<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            if (! Schema::hasColumn('institutes', 'organization_id')) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('organizations')
                    ->onDelete('set null');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_primary_principal')) {
                $table->boolean('is_primary_principal')
                    ->default(false)
                    ->after('is_delegated_admin');
            }

            if (! Schema::hasColumn('users', 'organization_id')) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('institute_id')
                    ->constrained('organizations')
                    ->onDelete('set null');
            }

            if (! Schema::hasColumn('users', 'current_institute_id')) {
                $table->foreignId('current_institute_id')
                    ->nullable()
                    ->after('organization_id')
                    ->constrained('institutes')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'current_institute_id')) {
                $table->dropForeign(['current_institute_id']);
                $table->dropColumn('current_institute_id');
            }
            if (Schema::hasColumn('users', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
            if (Schema::hasColumn('users', 'is_primary_principal')) {
                $table->dropColumn('is_primary_principal');
            }
        });

        Schema::table('institutes', function (Blueprint $table) {
            if (Schema::hasColumn('institutes', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });
    }
};
