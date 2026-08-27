<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentCourse;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    private int $targetCount;

    public function __construct()
    {
        $this->targetCount = (int) config('clinic.service_count.CLINIC_APPINTMENT_COUNT', 10);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::all();
        $patients = Patient::all();
        $treatmentCourses = TreatmentCourse::all();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            $this->command->warn('⚠️ Canno`t found Doctor`s or Patient`s well skip running AppointmentSeeder to save system.');
            return;
        }

        for ($i = 0; $i < $this->targetCount; $i++) {
            $doctorId = $doctors->random()->id;
            $patientId = $patients->random()->id;
            $date = fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d');
            $startTime = fake()->randomElement(['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00']);

            $matchingCourses = $treatmentCourses
                ->where('doctor_id', $doctorId)
                ->where('patient_id', $patientId);

            $matchingCourse = $matchingCourses->isNotEmpty() ? $matchingCourses->random() : null;


            Appointment::firstOrCreate(
                [
                    'doctor_id'           => $doctorId,
                    'appointment_date'    => $date,
                    'start_time'          => $startTime,
                ],
                [
                    'patient_id'          => $patients->random()->id,
                    'treatment_course_id' => $matchingCourse?->id,
                    'status'              => $i === 0
                        ? AppointmentStatus::Completed :
                        fake()->randomElement([
                            AppointmentStatus::Scheduled,
                            AppointmentStatus::Completed,
                            AppointmentStatus::Cancelled,
                        ]),
                    'reason_for_visit'    => fake()->sentence(4),
                ]
            );
        }

        $this->command->info('✅ Finished Run AppointmentSeeder Success and high performance with high security program.');
    }
}
