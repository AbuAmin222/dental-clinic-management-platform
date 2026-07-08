<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;


class DentalRecordFactory extends Factory
{
    protected $model = DentalRecord::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::inRandomOrder()->first()->id ?? Doctor::factory(),
            'patient_id' => Patient::inRandomOrder()->first()->id ?? Patient::factory(),
            'appointment_id' => Appointment::inRandomOrder()->first()->id ?? Appointment::factory(),
            'tooth_number' => (string) $this->faker->numberBetween(1, 32),
            'condition_type' => $this->faker->randomElement(['Healthy', 'Cavity', 'Missing', 'Filling']),
            'description' => $this->faker->paragraph(),
            'xray_image_path' => null,
        ];
    }
}
