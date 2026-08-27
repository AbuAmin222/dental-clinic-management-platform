<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DayOfWeek;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::all();

        if ($doctors->isEmpty()) {
            $this->command?->warn('⚠️ No doctors found — skipping DoctorScheduleSeeder. Run DoctorSeeder first.');
            return;
        }

        $workDays = [DayOfWeek::Sunday, DayOfWeek::Monday, DayOfWeek::Tuesday, DayOfWeek::Wednesday, DayOfWeek::Thursday];

        foreach ($doctors as $doctor) {
            foreach ($workDays as $day) {
                DoctorSchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day->value,
                        'start_time' => '09:00',
                    ],
                    [
                        'end_time' => '17:00',
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info('✅ DoctorScheduleSeeder: weekly schedules ensured for ' . $doctors->count() . ' doctor(s).');
    }
}
