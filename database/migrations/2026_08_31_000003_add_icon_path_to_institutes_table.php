<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('institutes')) {
            Schema::table('institutes', function (Blueprint $table) {
                if (!Schema::hasColumn('institutes', 'icon_path')) {
                    $table->string('icon_path')->nullable()->after('logo_path');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('institutes')) {
            Schema::table('institutes', function (Blueprint $table) {
                if (Schema::hasColumn('institutes', 'icon_path')) {
                    $table->dropColumn('icon_path');
                }
            });
        }
    }
};
