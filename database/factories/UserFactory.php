<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $identityNumber = fake()->numberBetween(111111111, 999999999);

        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->firstName('male'),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            // 'identity_number' => fake()->numberBetween(111111111, 999999999),
            'identity_number' => $identityNumber,
            'phone' => fake()->phoneNumber(),

            'date_of_birth' => fake()->date('Y-m-d', '-18 years'),

            'password' => static::$password ??= Hash::make($identityNumber),

            'identity_photo_path' => 'identities/default.png',

            'gender' => fake()->randomElement(['Male', 'Female']),
            'role' => 'patient',
            'is_active' => true,
            'remember_token' => Str::random(10),

            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'profile_photo_path' => null,
            'current_team_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should have a personal team.
     */
    public function withPersonalTeam(?callable $callback = null): static
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(fn(array $attributes, User $user) => [
                    'name' => $user->name . '\'s Team',
                    'user_id' => $user->id,
                    'personal_team' => true,
                ])
                ->when(is_callable($callback), $callback),
            'ownedTeams'
        );
    }
}
