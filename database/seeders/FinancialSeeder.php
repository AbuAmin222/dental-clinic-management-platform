<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Financial;
use Illuminate\Database\Seeder;

class FinancialSeeder extends Seeder
{
    private int $targetCount;

    public function __construct()
    {
        $this->targetCount = (int) config('clinic.user_count.CLINIC_FINANTIAL_USER_COUNT', 10);
    }

    public function run(): void
    {
        $existing = Financial::count();

        if ($existing >= $this->targetCount) {
            $this->command?->info("ℹ️ FinancialSeeder skipped — {$existing} financial officers already present (target " . $this->targetCount . ').');
            return;
        }

        Financial::factory()->count($this->targetCount - $existing)->create();

        $this->command?->info('✅ FinancialSeeder: financial officer population topped up to ' . $this->targetCount . '.');
    }
}
