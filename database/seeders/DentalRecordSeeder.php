<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\DentalRecord;
use Illuminate\Database\Seeder;

class DentalRecordSeeder extends Seeder
{

    private const TARGET_COUNT = 30;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $availableAppointments = Appointment::query()
            ->where('status', AppointmentStatus::Completed)
            ->whereDoesntHave('dentalRecord')
            ->inRandomOrder()
            ->limit(self::TARGET_COUNT)
            ->get();

        if ($availableAppointments->isEmpty()) {
            $this->command?->warn('⚠️ No completed appointments without a dental record found — skipping DentalRecordSeeder. Run AppointmentSeeder first.');
            return;
        }

        foreach ($availableAppointments as $appointment) {
            DentalRecord::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id' => $appointment->doctor_id,
                    'patient_id' => $appointment->patient_id,
                    'tooth_number' => (string) fake()->numberBetween(1, 32),
                    'condition_type' => fake()->randomElement(['Healthy', 'Cavity', 'Missing', 'Filling']),
                    'description' => fake()->paragraph(),
                    'xray_image_path' => null,
                ]
            );
        }

        $this->command?->info("✅ DentalRecordSeeder: created dental records for {$availableAppointments->count()} completed appointment(s).");
    }
}
