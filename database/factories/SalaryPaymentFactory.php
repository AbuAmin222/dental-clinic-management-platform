<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SalaryPaymentStatus;
use App\Models\Financial;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalaryPayment>
 */
class SalaryPaymentFactory extends Factory
{
    protected $model = SalaryPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $base = $this->faker->randomFloat(2, 4000, 12000);
        $deduction = $this->faker->randomFloat(2, 0, 300);
        $bonus = $this->faker->randomFloat(2, 0, 500);

        $periodStart = $this->faker->dateTimeBetween('-3 months', '-1 month')->modify('first day of this month');
        $periodEnd = (clone $periodStart)->modify('last day of this month');

        return [
            'user_id' => User::factory(),
            'processed_by_financial_id' => Financial::inRandomOrder()->first()?->id,
            'base_amount' => $base,
            'deduction_amount' => $deduction,
            'bonus_amount' => $bonus,
            'pay_period_start' => $periodStart->format('Y-m-d'),
            'pay_period_end' => $periodEnd->format('Y-m-d'),
            'status' => SalaryPaymentStatus::Paid,
            'paid_at' => $periodEnd,
            'notes' => null,
        ];
    }
}
