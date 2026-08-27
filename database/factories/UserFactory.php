<?php

namespace Database\Factories;

use App\Enums\UserRole;
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
            'identity_number' => $identityNumber,
            'phone' => fake()->phoneNumber(),

            'date_of_birth' => fake()->date('Y-m-d', '-18 years'),
            'address' => fake()->address(),

            'password' => static::$password ??= Hash::make($identityNumber),

            'identity_photo_path' => 'identities/default.png',

            'gender' => fake()->randomElement(['Male', 'Female']),
            // ⚠️ لا يوجد مفتاح 'role' هنا عمداً (إصلاح 2026-08-22): `users.role` لم يعد
            // عموداً فعلياً — كتابته هنا كانت تُتجاهَل بصمت من Eloquent (ليس في $fillable)،
            // ما كان يعني أن كل مستخدم يُنشأ بهذا الـ Factory بلا أي دور فعلي في
            // role_users. الدور الآن يُسنَد حصراً عبر state methods أدناه (->doctor()،
            // ->admin()، إلخ) التي تستدعي assignRole() فعلياً بعد الإنشاء.
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

    /**
     * إسناد دور بعد الإنشاء مباشرة — القاسم المشترك لكل state method أدناه.
     * User::booted()'s created hook لا يعمل هنا لأن لا يوجد عمود 'role' يقرأه أصلاً؛
     * afterCreating() هو المسار الصحيح الوحيد لإسناد دور فعلي من داخل Factory.
     */
    private function withRole(UserRole $role): static
    {
        return $this->afterCreating(function (User $user) use ($role): void {
            if ($user->roles()->doesntExist()) {
                $user->assignRole($role->value, isPrimary: true);
            }
        });
    }

    public function doctor(): static
    {
        return $this->withRole(UserRole::Doctor);
    }

    public function patient(): static
    {
        return $this->withRole(UserRole::Patient);
    }

    public function receptionist(): static
    {
        return $this->withRole(UserRole::Receptionist);
    }

    public function financial(): static
    {
        return $this->withRole(UserRole::Financial);
    }

    public function admin(): static
    {
        return $this->withRole(UserRole::Admin);
    }
}
