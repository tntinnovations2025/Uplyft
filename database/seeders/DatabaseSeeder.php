<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Platform super-admin account (admin@uplyft.com)
            GlobalAdminSeeder::class,

            // 2. Tri-campus Apex network (Cambridge, ACCA, Matric) with full roles
            ComprehensiveDemoNetworkSeeder::class,

            // 3. Dual-tenant dummy data (Apex College + Crescent Model School)
            UplyftDummyDataSeeder::class,

            // 4. System-level class/section taxonomy
            SystemClassSeeder::class,
        ]);
    }
}
