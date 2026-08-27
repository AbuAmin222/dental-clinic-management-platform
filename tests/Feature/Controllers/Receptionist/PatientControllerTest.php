<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Receptionist;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
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
    public function index_displays_patients_for_receptionist(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $response = $this->actingAs($user)->get('/receptionist/patients');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_returns_403_for_non_receptionist(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');

        $response = $this->actingAs($user)->get('/receptionist/patients');

        $response->assertStatus(403);
    }

    #[Test]
    public function index_filters_patients_by_search_term(): void
    {
        $user = User::factory()->create();
        $user->assignRole('receptionist');

        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();

        $searchTerm = $patient1->user->first_name;

        $response = $this->actingAs($user)->get("/receptionist/patients?search={$searchTerm}");

        $response->assertStatus(200);
    }

    #[Test]
    public function check_username_returns_available_when_not_taken(): void
    {
        $response = $this->postJson('/check-username', ['username' => 'newuser123']);

        $response->assertStatus(200);
        $response->assertJson(['valid' => true]);
    }

    #[Test]
    public function check_username_returns_taken_when_already_exists(): void
    {
        $user = User::factory()->create(['username' => 'existinguser']);

        $response = $this->postJson('/check-username', ['username' => 'existinguser']);

        $response->assertStatus(200);
        $response->assertJson(['valid' => false]);
    }

    #[Test]
    public function check_username_validates_required_field(): void
    {
        $response = $this->postJson('/check-username', []);

        $response->assertStatus(422);
    }
}
