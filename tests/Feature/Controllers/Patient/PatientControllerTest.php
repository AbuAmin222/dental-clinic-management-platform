<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Patient;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use App\Services\Appointment\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function index_displays_patient_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/patient/dashboard');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_returns_403_for_non_patient(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($user)->get('/patient/dashboard');

        $response->assertStatus(403);
    }

    #[Test]
    public function create_appointment_displays_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/patient/appointments/create');

        $response->assertStatus(200);
    }

    #[Test]
    public function create_appointment_returns_403_for_non_patient(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($user)->get('/patient/appointments/create');

        $response->assertStatus(403);
    }

    #[Test]
    public function store_appointment_creates_appointment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($user)->post('/patient/appointments', [
            'doctor_id' => $doctor->id,
            'appointment_date' => '2025-03-01',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'reason_for_visit' => 'Checkup',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'reason_for_visit' => 'Checkup',
        ]);
    }

    #[Test]
    public function checkout_invoice_displays_payment_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);
        $invoice = Invoice::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
        ]);

        $response = $this->actingAs($user)->get("/patient/invoice/{$invoice->id}/checkout");

        $response->assertStatus(200);
    }

    #[Test]
    public function checkout_invoice_returns_404_when_no_appointment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $patient = Patient::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'patient_id' => $patient->id,
            'appointment_id' => null,
        ]);

        $response = $this->actingAs($user)->get("/patient/invoice/{$invoice->id}/checkout");

        $response->assertNotFound();
    }
}
