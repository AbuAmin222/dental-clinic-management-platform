<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AdminAccessLevel;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'user_id'         => User::factory()->admin(),
            'employee_number' => 'ADM-' . fake()->unique()->numberBetween(1000, 9999),
            'access_level'    => AdminAccessLevel::Admin,
            'hiring_date'     => fake()->dateTimeBetween('-5 years', 'now'),
            'notes'           => null,
        ];
    }

    /**
     * حالة المسؤول الجذري — للاستخدام في الاختبارات والزرع الأولي فقط (أول حساب Admin
     * في النظام يجب أن يكون SuperAdmin دائماً، وإلا لا يوجد من يستطيع منح أي صلاحية أصلاً).
     */
    public function superAdmin(): static
    {
        return $this->state(fn(array $attributes) => [
            'access_level' => AdminAccessLevel::SuperAdmin,
        ]);
    }
}
