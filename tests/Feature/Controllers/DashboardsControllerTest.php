<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardsControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_redirects_to_role_dashboard(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect();
    }

    #[Test]
    public function index_returns_inertia_when_role_route_does_not_exist(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('patient');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_redirects_to_login_when_not_authenticated(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function index_redirects_when_user_has_admin_role(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect();
        $this->assertAuthenticated();
    }
}
