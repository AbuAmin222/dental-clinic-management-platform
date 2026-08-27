<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AdminAccessLevel;
use App\Enums\Gender;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 🔴 يزرع أول حساب SuperAdmin في النظام — كان غائباً تماماً حتى الآن. بدونه، بعد أي
 * `migrate:fresh --seed` لا يوجد أي حساب يستطيع تفعيل الحسابات المعلَّقة أو منح أي دور/
 * صلاحية لأي أحد — قفل كامل للنظام منذ اللحظة الأولى (بالضبط الخطر الذي بُنيت حماية
 * "آخر SuperAdmin" في UserPolicy لأجله، لكنه لا يُفيد إن لم يوجد SuperAdmin واحد أصلاً).
 *
 * بيانات الاعتماد تأتي من متغيرات بيئة حصراً (لا قيم افتراضية حسّاسة مكتوبة في الكود
 * نفسه، معيار 12-Factor نفسه المُطبَّق على بقية المشروع) — راجع .env.example.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (Admin::where('access_level', AdminAccessLevel::SuperAdmin)->exists()) {
            $this->command?->info('SuperAdmin already exists — skipping AdminSeeder.');

            return;
        }

        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@clinic.local');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (blank($password)) {
            $this->command?->error(
                'SUPER_ADMIN_PASSWORD is not set in .env — refusing to seed a SuperAdmin '
                . 'with a predictable/blank password. Set it and re-run: php artisan db:seed --class=AdminSeeder'
            );

            return;
        }

        $user = User::create([
            'first_name'          => 'Super',
            'middle_name'         => 'System',
            'last_name'           => 'Admin',
            'username'            => env('SUPER_ADMIN_USERNAME', 'superadmin'),
            'email'               => $email,
            'identity_number'     => env('SUPER_ADMIN_IDENTITY_NUMBER', '000000000'),
            'phone'               => env('SUPER_ADMIN_PHONE', '0590000000'),
            'password'            => Hash::make($password),
            'gender'              => Gender::Male->value,
            'date_of_birth'       => '1990-01-01',
            'address'             => null,
            'identity_photo_path' => null,
            'profile_photo_path'  => null,
            'is_active'           => true,
            'email_verified_at'   => now(),
        ]);

        $user->assignRole(UserRole::Admin->value, isPrimary: true);

        Admin::create([
            'user_id'         => $user->id,
            'employee_number' => 'ADM-0001',
            'access_level'    => AdminAccessLevel::SuperAdmin,
        ]);

        $this->command?->info("SuperAdmin seeded: {$email} — change the password immediately after first login.");
    }
}
