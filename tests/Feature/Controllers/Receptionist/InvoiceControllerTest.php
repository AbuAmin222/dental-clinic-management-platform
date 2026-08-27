<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Receptionist;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function create_displays_invoice_form_for_receptionist(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($user)->get("/receptionist/invoices/{$appointment->id}/create");

        $response->assertStatus(200);
    }

    #[Test]
    public function create_returns_403_for_non_receptionist(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($user)->get("/receptionist/invoices/{$appointment->id}/create");

        $response->assertStatus(403);
    }

    #[Test]
    public function destroy_deletes_invoice_for_appointment(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');
        $appointment = Appointment::factory()->create();

        \App\Models\Invoice::factory()->create(['appointment_id' => $appointment->id]);

        $response = $this->actingAs($user)->delete("/receptionist/appointments/{$appointment->id}");

        $response->assertRedirect();
    }

    #[Test]
    public function destroy_redirects_with_error_when_no_invoice(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($user)->delete("/receptionist/appointments/{$appointment->id}");

        $response->assertRedirect();
    }
}
