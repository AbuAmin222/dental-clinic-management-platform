<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserPermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    #[Test]
    public function index_returns_direct_and_role_permissions_for_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('doctor');
        $permission = Permission::factory()->create(['name' => 'extra.permission']);
        $user->givePermissionTo($permission, $admin);
        $user->load(['permissions', 'roles.permissions']);

        $response = $this->actingAs($admin)->getJson("/api/admin/users/{$user->id}/permissions");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    #[Test]
    public function store_grants_direct_permission_to_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('doctor');

        $permission = Permission::factory()->create(['name' => 'direct.permission']);

        $response = $this->actingAs($admin)->postJson("/api/admin/users/{$user->id}/permissions", [
            'permissions' => ['direct.permission'],
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data']);

        $this->assertTrue($user->fresh()->permissions->contains('name', 'direct.permission'));
    }

    #[Test]
    public function destroy_revokes_direct_permission_from_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('doctor');

        $permission = Permission::factory()->create(['name' => 'direct.permission']);
        $user->givePermissionTo($permission, $admin);

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$user->id}/permissions/{$permission->id}");

        $response->assertOk();
        $this->assertFalse($user->fresh()->permissions->contains('name', 'direct.permission'));
    }

    #[Test]
    public function store_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $target = User::factory()->create();
        $target->assignRole('doctor');

        $response = $this->actingAs($user)->postJson("/api/admin/users/{$target->id}/permissions", [
            'permissions' => ['some.permission'],
        ]);

        $response->assertStatus(403);
    }
}
