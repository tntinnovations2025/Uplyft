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
        Schema::table('daily_diaries', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_diaries', 'file_path')) {
                $table->string('file_path')->nullable()->after('content');
            }
            if (!Schema::hasColumn('daily_diaries', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('daily_diaries', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('daily_diaries', 'file_type')) {
                $table->string('file_type', 50)->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('daily_diaries', 'due_date')) {
                $table->date('due_date')->nullable()->after('assigned_date');
            }
            if (!Schema::hasColumn('daily_diaries', 'reminder_morning')) {
                $table->boolean('reminder_morning')->default(false)->after('due_date');
            }
            if (!Schema::hasColumn('daily_diaries', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('reminder_morning');
            }
        });

        // Ensure entry_type is flexible string
        try {
            Schema::table('daily_diaries', function (Blueprint $table) {
                $table->string('entry_type', 50)->default('homework')->change();
            });
        } catch (\Throwable $e) {
            // In case doctrine/dbal is missing or MySQL enum alter restriction, non-fatal
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_diaries', function (Blueprint $table) {
            $columns = [
                'file_path',
                'file_name',
                'file_size',
                'file_type',
                'due_date',
                'reminder_morning',
                'reminder_sent_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('daily_diaries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
