<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Receptionist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receptionist>
 */
class ReceptionistFactory extends Factory
{
    protected $model = Receptionist::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'user_id' => User::factory()->afterCreating(
            //     fn(User $user) => $user->assignRole(UserRole::Receptionist->value, true)
            // ),
            'user_id' => User::factory()->receptionist(),

            'department_id' => Department::inRandomOrder()->first()?->id ?? Department::factory(),
            'employee_number' => 'EMP-' . $this->faker->unique()->numberBetween(1000, 9999),
            'hiring_date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
        ];
    }
}
