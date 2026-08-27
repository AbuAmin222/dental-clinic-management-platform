<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subTotal = $this->faker->randomFloat(2, 100, 1000);
        $paidAmount = $this->faker->randomElement([0, $subTotal, $subTotal / 2]);

        return [
            'doctor_id'        => Doctor::inRandomOrder()->first()?->id ?? Doctor::factory(),
            'patient_id'       => Patient::inRandomOrder()->first()?->id ?? Patient::factory(),
            'appointment_id'   => Appointment::inRandomOrder()->first()?->id ?? Appointment::factory(),
            'sub_total'        => $subTotal,
            'tax_amount'       => 0,
            'discount_amount'  => 0,
            'total_amount'     => $subTotal,
            'paid_amount'      => $paidAmount,
            'due_amount'       => max(0, $subTotal - $paidAmount),
            'status'           => $paidAmount >= $subTotal ? InvoiceStatus::Paid : ($paidAmount > 0 ? InvoiceStatus::PartiallyPaid : InvoiceStatus::Pending),
            'due_date'         => $this->faker->dateTimeBetween('now', '+2 weeks'),
        ];
    }
}
