<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;
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

            'appointment_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),

            'start_time' => $this->faker->time('H:i'),
            'end_time' => $this->faker->time('H:i'),

            'status' => $this->faker->randomElement(['scheduled', 'completed', 'cancelled']),

            'reason_for_visit' => $this->faker->sentence(),
            'doctor_notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
