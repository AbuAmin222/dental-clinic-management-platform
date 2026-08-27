<?php

namespace Database\Seeders;

use App\Models\Receptionist;
use Illuminate\Database\Seeder;

class ReceptionistSeeder extends Seeder
{
    private int $targetCount;

    public function __construct()
    {
        $this->targetCount = (int) config('clinic.user_count.CLINIC_RECEPTIONIST_USER_COUNT', 10);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existing = Receptionist::count();

        if ($existing >= $this->targetCount) {
            $this->command?->info("ℹ️ ReceptionistSeeder skipped — {$existing} receptionists already present (target " . $this->targetCount . ').');
            return;
        }

        Receptionist::factory()->count($this->targetCount - $existing)->create();

        $this->command?->info('✅ ReceptionistSeeder: receptionist population topped up to ' . $this->targetCount . '.');
    }
}
