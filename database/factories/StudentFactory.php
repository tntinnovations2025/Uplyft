<?php

namespace Database\Factories;

use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'institute_id' => Institute::factory(),
            'academic_term_id' => AcademicTerm::factory(),
            'user_id' => User::factory(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'roll_number' => 'STD-' . fake()->unique()->numberBetween(1000, 9999),
            'date_of_birth' => fake()->dateTimeBetween('-19 years', '-15 years')->format('Y-m-d'),
            'guardian_tax_status' => 'non-filer',
            'admission_status' => 'enrolled',
            'annual_result_status' => 'pending',
            'previous_marks' => fake()->randomFloat(2, 60, 95),
            'base_fee' => 45000,
        ];
    }
}
