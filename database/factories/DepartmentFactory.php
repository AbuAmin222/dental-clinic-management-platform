<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Front Desk',
            'Billing',
            'Clinical Operations',
            'Radiology'
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => "Department responsible for " . $name,
            'is_active' => true,
        ];
    }
}
