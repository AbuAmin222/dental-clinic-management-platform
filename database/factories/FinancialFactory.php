<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Financial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Financial>
 */
class FinancialFactory extends Factory
{
    protected $model = Financial::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->afterCreating(
                fn(User $user) => $user->assignRole(UserRole::Financial->value, true)
            ),
            'employee_number' => 'FIN-' . $this->faker->unique()->numberBetween(1000, 9999),
            'hiring_date' => $this->faker->dateTimeBetween('-6 years', 'now')->format('Y-m-d'),
            'years_experience' => $this->faker->numberBetween(1, 15),
            'specialization' => $this->faker->randomElement([
                'Accounts Receivable',
                'Payroll Administration',
                'Insurance Billing',
                'Revenue Cycle Management',
            ]),
            'metadata' => [
                'department' => 'Finance & Billing',
            ],
            'is_profile_completed' => true,
        ];
    }
}
