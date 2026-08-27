<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentCourse;
use Illuminate\Database\Seeder;

class TreatmentCourseSeeder extends Seeder
{
    private const TARGET_COUNT = 20;

    public function run(): void
    {
        $doctors = Doctor::all();
        $patients = Patient::all();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            $this->command?->warn('⚠️ No doctors or patients found — skipping TreatmentCourseSeeder.');
            return;
        }

        $existing = TreatmentCourse::count();

        if ($existing >= self::TARGET_COUNT) {
            $this->command?->info("ℹ️ TreatmentCourseSeeder skipped — {$existing} treatment courses already present (target " . self::TARGET_COUNT . ').');
            return;
        }

        $this->command?->info("✍️ TreatmentCourseSeeder creating — {$existing} treatment courses (target " . self::TARGET_COUNT . " - " . self::TARGET_COUNT / 2 . ').');

        for ($i = $existing; $i < self::TARGET_COUNT; $i++) {
            TreatmentCourse::factory()->create([
                'patient_id' => $patients->random(1)->id,
                'doctor_id' => $doctors->random(1)->id,
            ]);
        }

        $this->command?->info('✅ TreatmentCourseSeeder: treatment course population topped up to ' . self::TARGET_COUNT . '.');
    }
}
