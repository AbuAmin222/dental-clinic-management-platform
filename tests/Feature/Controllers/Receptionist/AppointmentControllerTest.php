<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Receptionist;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function index_displays_appointments_for_receptionist(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $response = $this->actingAs($user)->get('/receptionist/appointments');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_filters_by_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $response = $this->actingAs($user)->get('/receptionist/appointments?status=scheduled');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_filters_by_search_term(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $response = $this->actingAs($user)->get('/receptionist/appointments?search=test');

        $response->assertStatus(200);
    }

    #[Test]
    public function create_displays_form_for_authorized_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $response = $this->actingAs($user)->get('/receptionist/appointments/create');

        $response->assertStatus(200);
    }

    #[Test]
    public function create_returns_403_for_non_receptionist(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');

        $response = $this->actingAs($user)->get('/receptionist/appointments/create');

        $response->assertStatus(403);
    }

    #[Test]
    public function update_status_changes_appointment_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($user)->patch("/receptionist/appointments/{$appointment->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $this->assertSame(AppointmentStatus::Completed->value, $appointment->fresh()->status);
    }

    #[Test]
    public function update_status_validates_status_field(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($user)->patch("/receptionist/appointments/{$appointment->id}/status", [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }
}
