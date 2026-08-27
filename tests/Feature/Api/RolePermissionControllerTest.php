<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RolePermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    #[Test]
    public function index_returns_permissions_for_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $role = Role::where('name', 'financial')->first();

        $response = $this->actingAs($user)->getJson("/api/admin/roles/{$role->id}/permissions");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    #[Test]
    public function store_grants_permission_to_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $role = Role::where('name', 'doctor')->first();
        $permission = Permission::factory()->create(['name' => 'test.permission']);

        $response = $this->actingAs($user)->postJson("/api/admin/roles/{$role->id}/permissions", [
            'permissions' => ['test.permission'],
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data']);

        $this->assertTrue($role->fresh()->permissions->contains('name', 'test.permission'));
    }

    #[Test]
    public function destroy_revokes_permission_from_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $role = Role::where('name', 'doctor')->first();
        $permission = Permission::factory()->create(['name' => 'test.permission']);
        $role->givePermissionTo($permission);

        $response = $this->actingAs($user)->deleteJson("/api/admin/roles/{$role->id}/permissions/{$permission->id}");

        $response->assertOk();
        $this->assertFalse($role->fresh()->permissions->contains('name', 'test.permission'));
    }

    #[Test]
    public function store_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $role = Role::where('name', 'financial')->first();

        $response = $this->actingAs($user)->postJson("/api/admin/roles/{$role->id}/permissions", [
            'permissions' => ['test.permission'],
        ]);

        $response->assertStatus(403);
    }
}
