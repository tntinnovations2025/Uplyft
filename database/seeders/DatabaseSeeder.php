<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        \App\Models\Institute::create([
            'name' => 'Uplyft Academy',
            'logo_path' => 'logos/uplyft_academy.png',
            'settings' => [
                'base_admission_fee' => 15000.00,
                'filer_tax_rate' => 0.04,
                'non_filer_tax_rate' => 0.12,
            ],
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
