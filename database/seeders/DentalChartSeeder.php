<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DentalChart;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class DentalChartSeeder extends Seeder
{
    /** How many teeth to chart per patient. */
    private const TEETH_PER_PATIENT = 4;

    public function run(): void
    {
        $doctors = Doctor::all();
        $patients = Patient::inRandomOrder()->limit(20)->get();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            $this->command?->warn('⚠️ No doctors or patients found — skipping DentalChartSeeder.');
            return;
        }

        $conditions = ['Healthy', 'Cavity', 'Filled', 'Missing', 'Crowned', 'Root Canal'];

        foreach ($patients as $patient) {
            $doctor = $doctors->random();
            $teeth = collect(range(1, 32))->random(self::TEETH_PER_PATIENT);

            foreach ($teeth as $tooth) {
                DentalChart::upsertForTooth(
                    patientId: $patient->id,
                    doctorId: $doctor->id,
                    toothNumber: (int) $tooth,
                    condition: fake()->randomElement($conditions),
                    notes: fake()->optional()->sentence(),
                );
            }
        }

        $this->command?->info("✅ DentalChartSeeder: dental charts ensured for {$patients->count()} patient(s).");
    }
}
