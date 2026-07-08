<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'doctor']),
            'specialization_id' => Specialization::inRandomOrder()->first()->id,
            'license_number' => 'DOC-' . fake()->unique()->numberBetween(1000, 9999),
            'experience_years' => fake()->numberBetween(1, 20),
            'bio' => fake()->sentence(),
        ];
    }
}
