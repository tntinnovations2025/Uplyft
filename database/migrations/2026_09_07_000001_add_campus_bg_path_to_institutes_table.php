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
                if (!Schema::hasColumn('institutes', 'campus_bg_path')) {
                    $table->string('campus_bg_path')->nullable()->after('icon_path');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('institutes')) {
            Schema::table('institutes', function (Blueprint $table) {
                if (Schema::hasColumn('institutes', 'campus_bg_path')) {
                    $table->dropColumn('campus_bg_path');
                }
            });
        }
    }
};
