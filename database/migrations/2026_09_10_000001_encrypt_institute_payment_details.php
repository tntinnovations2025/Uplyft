<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Encrypt tenant payment credentials at rest.
     *
     * The InstituteSetting model exposes these columns through Laravel's
     * built-in 'encrypted' cast. This migration:
     *   1. widens the columns so ciphertext fits comfortably, and
     *   2. re-encrypts any rows that still hold plaintext from before the
     *      cast was introduced (idempotent — ciphertext rows are skipped).
     */
    private const SENSITIVE_COLUMNS = ['bank_account_number', 'bank_iban'];

    public function up(): void
    {
        if (! Schema::hasTable('institute_settings')) {
            return;
        }

        Schema::table('institute_settings', function (Blueprint $table) {
            foreach (self::SENSITIVE_COLUMNS as $col) {
                if (Schema::hasColumn('institute_settings', $col)) {
                    $table->string($col, 500)->nullable()->change();
                }
            }
        });

        DB::table('institute_settings')
            ->select(['id', ...self::SENSITIVE_COLUMNS])
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach (self::SENSITIVE_COLUMNS as $col) {
                        $value = $row->{$col};

                        if ($value === null || $value === '') {
                            continue;
                        }

                        $isCipher = false;
                        try {
                            Crypt::decryptString($value);
                            $isCipher = true;
                        } catch (\Throwable $e) {
                            $isCipher = false;
                        }

                        if (! $isCipher) {
                            $updates[$col] = Crypt::encryptString($value);
                        }
                    }

                    if ($updates) {
                        DB::table('institute_settings')->where('id', $row->id)->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('institute_settings')) {
            return;
        }

        DB::table('institute_settings')
            ->select(['id', ...self::SENSITIVE_COLUMNS])
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach (self::SENSITIVE_COLUMNS as $col) {
                        $value = $row->{$col};

                        if ($value === null || $value === '') {
                            continue;
                        }

                        $isCipher = false;
                        try {
                            $plain = Crypt::decryptString($value);
                            $isCipher = true;
                        } catch (\Throwable $e) {
                            $isCipher = false;
                        }

                        if ($isCipher) {
                            $updates[$col] = $plain;
                        }
                    }

                    if ($updates) {
                        DB::table('institute_settings')->where('id', $row->id)->update($updates);
                    }
                }
            });

        Schema::table('institute_settings', function (Blueprint $table) {
            foreach (self::SENSITIVE_COLUMNS as $col) {
                if (Schema::hasColumn('institute_settings', $col)) {
                    $table->string($col, 255)->nullable()->change();
                }
            }
        });
    }
};