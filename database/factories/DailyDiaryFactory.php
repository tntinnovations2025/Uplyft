<?php

namespace Database\Factories;

use App\Models\DailyDiary;
use App\Models\Institute;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyDiary>
 */
class DailyDiaryFactory extends Factory
{
    protected $model = DailyDiary::class;

    public function definition(): array
    {
        $assignedDate = fake()->dateTimeBetween('-2 days', 'now')->format('Y-m-d');

        return [
            'institute_id' => Institute::factory(),
            'class_section_id' => ClassSection::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => User::factory(),
            'entry_type' => fake()->randomElement(['homework', 'test_alert', 'announcement', 'classwork']),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(2),
            'assigned_date' => $assignedDate,
            'expires_at' => \Carbon\Carbon::parse($assignedDate)->addDays(14),
            'is_active' => true,
        ];
    }
}
