<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    private int $targetCount;

    public function __construct()
    {
        $this->targetCount = (int) config('clinic.user_count.CLINIC_DOCTOR_USER_COUNT', 10);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existing = Doctor::count();

        if ($existing >= $this->targetCount) {
            $this->command?->info("ℹ️ DoctorSeeder skipped — {$existing} doctors already present (target " . $this->targetCount . ').');
            return;
        }

        Doctor::factory()->count($this->targetCount - $existing)->create();

        $this->command?->info('✅ DoctorSeeder: doctor population topped up to ' . $this->targetCount . '.');
    }
}
