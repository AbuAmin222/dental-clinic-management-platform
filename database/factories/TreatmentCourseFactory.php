<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TreatmentCourseStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TreatmentCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TreatmentCourse>
 */
class TreatmentCourseFactory extends Factory
{
    protected $model = TreatmentCourse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $planned = $this->faker->numberBetween(2, 6);

        return [
            'patient_id' => Patient::inRandomOrder()->first()?->id ?? Patient::factory(),
            'doctor_id' => Doctor::inRandomOrder()->first()?->id ?? Doctor::factory(),
            'title' => $this->faker->randomElement([
                'Root Canal Treatment',
                'Orthodontic Alignment',
                'Full Mouth Rehabilitation',
                'Periodontal Therapy',
                'Dental Implant Course',
            ]),
            'tooth_number' => $this->faker->numberBetween(1, 32),
            'planned_sessions_count' => $planned,
            'completed_sessions_count' => 0,
            'status' => TreatmentCourseStatus::Ongoing,
        ];
    }
}
