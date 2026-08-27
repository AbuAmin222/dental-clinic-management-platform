<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Pricing;
use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    private int $targetCount;

    public function __construct()
    {
        $this->targetCount = (int) config('clinic.service_count.CLINIC_PRICING_COUNT', 10);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Doctor::doesntExist()) {
            $this->command?->warn('⚠️ No doctors found — skipping PricingSeeder. Run DoctorSeeder first.');
            return;
        }

        $existing = Pricing::count();

        if ($existing >= $this->targetCount) {
            $this->command?->info("ℹ️ PricingSeeder skipped — {$existing} pricing rows already present (target " . $this->targetCount . ').');
            return;
        }

        Pricing::factory()->count($this->targetCount - $existing)->create();

        $this->command?->info('✅ PricingSeeder: pricing population topped up to ' . $this->targetCount . '.');
    }
}
