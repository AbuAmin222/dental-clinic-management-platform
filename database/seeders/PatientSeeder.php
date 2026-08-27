<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    private int $targetCount;

    public function __construct()
    {
        $this->targetCount = (int) config('clinic.user_count.CLINIC_PATIENT_USER_COUNT', 10);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existing = Patient::count();

        if ($existing >= $this->targetCount) {
            $this->command?->info("ℹ️ PatientSeeder skipped — {$existing} patients already present (target " . $this->targetCount . ').');
            return;
        }

        Patient::factory()->count($this->targetCount - $existing)->create();

        $this->command?->info('✅ PatientSeeder: patient population topped up to ' . $this->targetCount . '.');
    }
}
