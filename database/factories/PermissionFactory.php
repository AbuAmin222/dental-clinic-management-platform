<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->slug(3),
            'display_name' => $this->faker->words(3, true),
            'group' => $this->faker->randomElement(['invoices', 'appointments', 'patients', 'users', 'settings']),
        ];
    }
}
