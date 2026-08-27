<?php

namespace Database\Factories;

use App\Enums\BloodGroup;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'user_id' => User::factory()->afterCreating(
            //     fn(User $user) => $user->assignRole(UserRole::Patient->value, true)
            // ),

            'user_id' => User::factory()->patient()->create()->id,

            'blood_group' => $this->faker->randomElement(BloodGroup::values()),

            'allergies' => $this->faker->optional()->sentence(),
            'chronic_diseases' => $this->faker->optional()->words(2, true),

            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->numerify('059#######'),

            'medical_notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
