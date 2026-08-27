<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SalaryPaymentStatus;
use App\Enums\UserRole;
use App\Models\Financial;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalaryPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::whereHas('roles', fn($q) => $q->whereIn('name', UserRole::staffRoleValues()))->get();

        if ($staff->isEmpty()) {
            $this->command?->warn('⚠️ No staff users found — skipping SalaryPaymentSeeder.');
            return;
        }

        $financialOfficer = Financial::first();

        foreach ($staff as $user) {
            $base = $user->base_salary ?? fake()->randomFloat(2, 4000, 12000);

            // One paid slip for each of the last 2 months per staff member.
            for ($monthsAgo = 2; $monthsAgo >= 1; $monthsAgo--) {
                $periodStart = now()->subMonths($monthsAgo)->startOfMonth();
                $periodEnd = (clone $periodStart)->endOfMonth();

                SalaryPayment::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'pay_period_start' => $periodStart->format('Y-m-d'),
                        'pay_period_end' => $periodEnd->format('Y-m-d'),
                    ],
                    [
                        'processed_by_financial_id' => $financialOfficer?->id,
                        'base_amount' => $base,
                        'deduction_amount' => 0,
                        'bonus_amount' => 0,
                        'amount'                   => $base,
                        'status' => SalaryPaymentStatus::Paid,
                        'paid_at' => $periodEnd,
                    ]
                );
            }
        }

        $this->command?->info("✅ SalaryPaymentSeeder: salary history ensured for {$staff->count()} staff member(s).");
    }
}
