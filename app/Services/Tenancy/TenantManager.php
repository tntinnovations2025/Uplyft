<?php

namespace App\Services\Tenancy;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PDO;

class TenantManager
{
    public function __construct(
        protected string $prefix = 'institute',
        protected string $defaultDatabase = '',
    ) {
        $this->prefix = config('database.connections.institute.database') ? 'institute' : 'institute';
        $this->defaultDatabase = config('database.connections.institute.database', '');
    }

    /**
     * Point the institute connection at the given institute's database.
     */
    public function switchTo(int $instituteId): void
    {
        $database = $this->databaseName($instituteId);

        Config::set('database.connections.'.$this->prefix.'.database', $database);

        if (DB::connection($this->prefix)->getPdo() instanceof PDO) {
            DB::purge($this->prefix);
        }
    }

    /**
     * Build the MySQL database name for an institute.
     * e.g. prefix "uplifyt_inst_" + id "01" => "uplifyt_inst_01".
     */
    public function databaseName(int $instituteId): string
    {
        return env('INSTITUTE_DB_PREFIX', 'uplifyt_inst_').str_pad((string) $instituteId, 2, '0', STR_PAD_LEFT);
    }
}
