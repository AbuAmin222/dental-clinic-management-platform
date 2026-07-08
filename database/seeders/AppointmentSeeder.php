<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();
        $patients = Patient::all();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            $this->command->warn('⚠️ لم يتم العثور على أطباء أو مرضى! سيتم تخطي تشغيل AppointmentSeeder لحماية النظام.');
            return;
        }

        for ($i = 0; $i < 100; $i++) {
            $doctorId = $doctors->random()->id;
            $date = fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d');
            $startTime = fake()->randomElement(['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00']);

            Appointment::firstOrCreate(
                [
                    'doctor_id'        => $doctorId,
                    'appointment_date' => $date,
                    'start_time'       => $startTime,
                ],
                [
                    'patient_id'       => $patients->random()->id,
                    'status'           => fake()->randomElement(['scheduled', 'completed', 'cancelled']),
                    'reason_for_visit' => fake()->sentence(4),
                ]
            );
        }

        $this->command->info('✅ تم تشغيل AppointmentSeeder بنجاح وبأداء عالي الأمان البرمجي.');
    }
}
