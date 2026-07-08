<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Pricing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pricing>
 */
class PricingFactory extends Factory
{
    protected $model = Pricing::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::inRandomOrder()->first()->id ?? Doctor::factory(),
            'service_name' => $this->faker->randomElement(['Cleaning', 'Filling', 'X-Ray', 'Whitening']),
            'amount' => $this->faker->randomFloat(2, 20, 500),
        ];
    }
}
