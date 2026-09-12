<?php

namespace Database\Factories;

use App\Models\Institute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Institute>
 */
class InstituteFactory extends Factory
{
    protected $model = Institute::class;

    public function definition(): array
    {
        $name = fake()->unique()->company() . ' Academy';
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'subscription_tier' => 'premium',
            'subscription_starts_at' => now()->startOfYear(),
            'subscription_expires_at' => now()->addYear(),
            'is_active' => true,
            'is_onboarded' => true,
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'country' => 'Pakistan',
            'education_systems' => ['higher_sec', 'acca'],
        ];
    }
}
