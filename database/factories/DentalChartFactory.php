<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DentalChart;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DentalChart>
 */
class DentalChartFactory extends Factory
{
    protected $model = DentalChart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::inRandomOrder()->first()?->id ?? Patient::factory(),
            'doctor_id' => Doctor::inRandomOrder()->first()?->id ?? Doctor::factory(),
            'tooth_number' => $this->faker->numberBetween(1, 32),
            'condition' => $this->faker->randomElement(['Healthy', 'Cavity', 'Filled', 'Missing', 'Crowned', 'Root Canal']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
