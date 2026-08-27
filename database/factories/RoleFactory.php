<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug(1),
            'display_name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'profile_type' => $this->faker->randomElement(['doctor', 'patient', 'receptionist', 'financial', null]),
            'is_system' => false,
        ];
    }
}
