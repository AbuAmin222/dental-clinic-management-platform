<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\TreatmentCourse;
use App\Enums\TreatmentCourseStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TreatmentCourseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function casts_tooth_number_and_session_counts_as_integer(): void
    {
        $course = TreatmentCourse::factory()->create([
            'tooth_number' => '15',
            'planned_sessions_count' => '5',
            'completed_sessions_count' => '3',
        ]);

        $this->assertSame(15, $course->tooth_number);
        $this->assertSame(5, $course->planned_sessions_count);
        $this->assertSame(3, $course->completed_sessions_count);
    }

    #[Test]
    public function casts_status_as_enum(): void
    {
        $course = TreatmentCourse::factory()->create(['status' => TreatmentCourseStatus::Ongoing]);

        $this->assertEquals(TreatmentCourseStatus::Ongoing, $course->status);
    }

    #[Test]
    public function uses_soft_deletes(): void
    {
        $course = TreatmentCourse::factory()->create();
        $course->delete();

        $this->assertSoftDeleted($course);
    }

    #[Test]
    public function appointments_relationship(): void
    {
        $course = TreatmentCourse::factory()->create([
            'planned_sessions_count' => 1,
        ]);
        Appointment::factory()->create([
            'treatment_course_id' => $course->id,
            'status' => AppointmentStatus::Completed,
        ]);

        $this->assertCount(1, $course->appointments);
    }

    #[Test]
    public function recalculate_sets_completed_status_when_sessions_met(): void
    {
        $course = TreatmentCourse::factory()->create([
            'planned_sessions_count' => 2,
        ]);

        Appointment::factory()->create([
            'treatment_course_id' => $course->id,
            'status' => AppointmentStatus::Completed,
        ]);
        Appointment::factory()->create([
            'treatment_course_id' => $course->id,
            'status' => AppointmentStatus::Completed,
        ]);

        $course->recalculateSessionsCount();
        $course = $course->fresh();

        $this->assertSame(TreatmentCourseStatus::Completed, $course->status);
        $this->assertSame(2, $course->completed_sessions_count);
    }

    #[Test]
    public function recalculate_does_not_complete_when_sessions_incomplete(): void
    {
        $course = TreatmentCourse::factory()->create([
            'planned_sessions_count' => 3,
        ]);

        Appointment::factory()->create([
            'treatment_course_id' => $course->id,
            'status' => AppointmentStatus::Completed,
        ]);

        $course->recalculateSessionsCount();
        $course = $course->fresh();

        $this->assertSame(TreatmentCourseStatus::Ongoing, $course->status);
        $this->assertSame(1, $course->completed_sessions_count);
    }

    #[Test]
    public function recalculate_counts_only_completed_appointments(): void
    {
        $course = TreatmentCourse::factory()->create([
            'planned_sessions_count' => 5,
        ]);

        Appointment::factory()->create([
            'treatment_course_id' => $course->id,
            'status' => AppointmentStatus::Completed,
        ]);
        Appointment::factory()->create([
            'treatment_course_id' => $course->id,
            'status' => AppointmentStatus::Cancelled,
        ]);
        Appointment::factory()->create([
            'treatment_course_id' => $course->id,
            'status' => AppointmentStatus::Scheduled,
        ]);

        $course->recalculateSessionsCount();
        $course = $course->fresh();

        $this->assertSame(1, $course->completed_sessions_count);
    }
}
