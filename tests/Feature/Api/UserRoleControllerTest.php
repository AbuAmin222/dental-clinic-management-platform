<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\AdminAccessLevel;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function index_returns_roles_for_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($admin)->getJson("/api/admin/users/{$user->id}/roles");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    #[Test]
    public function store_assigns_new_role_to_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($admin)->postJson("/api/admin/users/{$user->id}/roles", [
            'role' => 'financial',
        ]);

        $response->assertOk();
        $user->load('roles');
        $this->assertTrue($user->hasRole('financial'));
    }

    #[Test]
    public function destroy_removes_role_from_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('doctor');
        $user->assignRole('patient');

        $role = Role::where('name', 'patient')->first();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$user->id}/roles/{$role->id}");

        $response->assertOk();
        $user->load('roles');
        $this->assertFalse($user->hasRole('patient'));
    }

    #[Test]
    public function destroy_prevents_removing_last_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Admin::factory()->superAdmin()->create(['user_id' => $admin->id]);

        $user = User::factory()->create();
        $user->assignRole('admin');
        Admin::factory()->superAdmin()->create(['user_id' => $user->id]);

        $role = Role::where('name', 'admin')->first();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$user->id}/roles/{$role->id}");

        $response->assertStatus(500);
    }

    #[Test]
    public function store_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $target = User::factory()->create();
        $target->assignRole('doctor');

        $response = $this->actingAs($user)->postJson("/api/admin/users/{$target->id}/roles", [
            'role' => 'patient',
        ]);

        $response->assertStatus(403);
    }
}
