<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 100, 1000);
        return [
            'doctor_id' => Doctor::inRandomOrder()->first()->id ?? Doctor::factory(),
            'patient_id' => Patient::inRandomOrder()->first()->id ?? Patient::factory(),
            'appointment_id' => Appointment::inRandomOrder()->first()->id ?? Appointment::factory(),
            'total_amount' => $total,
            'paid_amount' => $this->faker->randomElement([0, $total, $total / 2]),
            'status' => $this->faker->randomElement(['unpaid', 'partially_paid', 'paid']),
            'due_date' => $this->faker->dateTimeBetween('now', '+2 weeks'),
        ];
    }
}
