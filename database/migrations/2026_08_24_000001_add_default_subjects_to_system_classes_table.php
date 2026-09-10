<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('system_classes', 'default_subjects')) {
            Schema::table('system_classes', function (Blueprint $table) {
                $table->json('default_subjects')->nullable()->after('education_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('system_classes', 'default_subjects')) {
            Schema::table('system_classes', function (Blueprint $table) {
                $table->dropColumn('default_subjects');
            });
        }
    }
};
