<?php

namespace Database\Factories;

use App\Models\AcademicTrack;
use App\Models\Institute;
use App\Models\InstituteClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicTrack>
 */
class AcademicTrackFactory extends Factory
{
    protected $model = AcademicTrack::class;

    public function definition(): array
    {
        return [
            'institute_id' => Institute::factory(),
            'class_id' => InstituteClass::factory(),
            'track_name' => fake()->randomElement(['FSc Pre-Medical', 'ICS (Physics)', 'ICS (Stats)', 'General Science']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
