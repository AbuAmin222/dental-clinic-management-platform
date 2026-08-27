<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function fills_name_display_name_description_profile_type(): void
    {
        $role = Role::create([
            'name' => 'custom',
            'display_name' => 'Custom Role',
            'description' => 'A custom role',
            'profile_type' => 'custom',
            'is_system' => false,
        ]);

        $this->assertSame('custom', $role->name);
        $this->assertSame('Custom Role', $role->display_name);
        $this->assertFalse($role->is_system);
    }

    #[Test]
    public function casts_is_system_as_boolean(): void
    {
        $role = Role::create([
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'is_system' => true,
        ]);

        $this->assertTrue($role->is_system);
    }

    #[Test]
    public function users_relationship(): void
    {
        $role = Role::where('name', 'doctor')->first();
        $user = User::factory()->create();
        $user->assignRole($role, true);

        $this->assertCount(1, $role->users);
    }

    #[Test]
    public function permissions_relationship(): void
    {
        $role = Role::where('name', 'admin')->first();

        $this->assertIsIterable($role->permissions);
    }

    #[Test]
    public function give_permission_to_grants_permission_to_role(): void
    {
        $role = Role::where('name', 'doctor')->first();
        $permission = \App\Models\Permission::factory()->create(['name' => 'edit-records']);

        $role->givePermissionTo($permission);

        $this->assertTrue($role->hasPermission('edit-records'));
    }

    #[Test]
    public function give_permission_to_by_string(): void
    {
        $role = Role::where('name', 'doctor')->first();
        \App\Models\Permission::factory()->create(['name' => 'view-reports']);

        $role->givePermissionTo('view-reports');

        $this->assertTrue($role->hasPermission('view-reports'));
    }

    #[Test]
    public function revoke_permission_removes_permission_from_role(): void
    {
        $role = Role::where('name', 'doctor')->first();
        $permission = \App\Models\Permission::factory()->create(['name' => 'edit-records']);
        $role->givePermissionTo($permission);

        $role->revokePermissionTo('edit-records');
        $role->load('permissions');

        $this->assertFalse($role->hasPermission('edit-records'));
    }

    #[Test]
    public function has_permission_returns_true_when_assigned(): void
    {
        $role = Role::where('name', 'doctor')->first();
        $permission = \App\Models\Permission::factory()->create(['name' => 'manage-patients']);
        $role->givePermissionTo($permission);
        $role->load('permissions');

        $this->assertTrue($role->hasPermission('manage-patients'));
    }

    #[Test]
    public function has_permission_returns_false_when_not_assigned(): void
    {
        $role = Role::where('name', 'doctor')->first();
        $role->load('permissions');

        $this->assertFalse($role->hasPermission('non-existent-permission'));
    }
}
