<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Database\Seeders\RoleSeeder::class && \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => \Database\Seeders\RoleSeeder::class]);
    }

    #[Test]
    public function fillable_attributes_are_mass_assignable(): void
    {
        $this->assertContains('email', (new User())->getFillable());
        $this->assertContains('first_name', (new User())->getFillable());
        $this->assertContains('password', (new User())->getFillable());
    }

    #[Test]
    public function hidden_attributes_are_excluded_from_array(): void
    {
        $user = User::factory()->create([]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $array);
    }

    #[Test]
    public function appends_virtual_attributes(): void
    {
        $user = User::factory()->create([]);

        $array = $user->toArray();

        $this->assertArrayHasKey('full_name', $array);
    }

    #[Test]
    public function casts_password_as_hashed(): void
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotSame('plaintext', $user->fresh()->password);
    }

    #[Test]
    public function full_name_accessor_combines_names(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Doe',
        ]);

        $this->assertSame('John Michael Doe', $user->full_name);
    }

    #[Test]
    public function full_name_accessor_trims_whitespace(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'middle_name' => '',
            'last_name' => 'Doe',
        ]);

        $this->assertSame('John Doe', $user->full_name);
    }

    #[Test]
    public function role_accessor_returns_primary_role_name(): void
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'doctor')->first();
        $user->assignRole($role, true);

        $this->assertSame('doctor', $user->role);
    }

    #[Test]
    public function role_accessor_returns_null_when_no_role_assigned(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->role);
    }

    #[Test]
    public function assign_role_assigns_role_to_user(): void
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'doctor')->first();

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('doctor'));
    }

    #[Test]
    public function assign_role_by_string(): void
    {
        $user = User::factory()->create();

        $user->assignRole('doctor');

        $this->assertTrue($user->hasRole('doctor'));
    }

    #[Test]
    public function first_assigned_role_becomes_primary(): void
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'doctor')->first();

        $user->assignRole($role);

        $primaryRole = $user->primaryRole();
        $this->assertSame('doctor', $primaryRole->name);
        $this->assertTrue($user->roles()->wherePivot('is_primary', true)->exists());
    }

    #[Test]
    public function assigning_new_primary_removes_previous_primary(): void
    {
        $user = User::factory()->create();
        $role1 = Role::where('name', 'doctor')->first();
        $role2 = Role::where('name', 'patient')->first();

        $user->assignRole($role1, true);
        $user->assignRole($role2, true);

        $primaryRoles = $user->roles()->wherePivot('is_primary', true)->get();

        $this->assertCount(1, $primaryRoles);
        $this->assertSame('patient', $primaryRoles->first()->name);
    }

    #[Test]
    public function has_role_returns_true_for_assigned_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $this->assertTrue($user->hasRole('doctor'));
    }

    #[Test]
    public function has_role_returns_false_for_unassigned_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $this->assertFalse($user->hasRole('financial'));
    }

    #[Test]
    public function has_role_accepts_array_of_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $this->assertTrue($user->hasRole(['doctor', 'patient']));
    }

    #[Test]
    public function remove_role_detaches_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $this->assertTrue($user->hasRole('doctor'));

        $user->removeRole('doctor');
        $user->load('roles');

        $this->assertFalse($user->hasRole('doctor'));
    }

    #[Test]
    public function give_permission_to_grants_direct_permission(): void
    {
        $user = User::factory()->create();
        $permission = \App\Models\Permission::factory()->create(['name' => 'edit-records']);

        $user->givePermissionTo($permission);

        $this->assertTrue($user->hasPermissionTo('edit-records'));
    }

    #[Test]
    public function give_permission_to_by_string(): void
    {
        $user = User::factory()->create();
        $permission = \App\Models\Permission::factory()->create(['name' => 'view-reports']);

        $user->givePermissionTo('view-reports');

        $this->assertTrue($user->hasPermissionTo('view-reports'));
    }

    #[Test]
    public function revoke_permission_removes_direct_permission(): void
    {
        $user = User::factory()->create();
        $permission = \App\Models\Permission::factory()->create(['name' => 'edit-records']);

        $user->givePermissionTo($permission);
        $user->revokePermissionTo('edit-records');

        $this->assertFalse($user->hasPermissionTo('edit-records'));
    }

    #[Test]
    public function has_permission_to_returns_true_for_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->load('roles.permissions');

        $this->assertTrue($user->hasPermissionTo('any-permission'));
    }

    #[Test]
    public function has_permission_to_checks_role_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::where('name', 'doctor')->first();
        $permission = \App\Models\Permission::factory()->create(['name' => 'write-records']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);
        $user->load('roles.permissions');

        $this->assertTrue($user->hasPermissionTo('write-records'));
    }

    #[Test]
    public function has_permission_to_returns_false_when_not_granted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');
        $user->load('roles.permissions');

        $this->assertFalse($user->hasPermissionTo('write-records'));
    }

    #[Test]
    public function profile_photo_url_attribute_returns_url_when_path_set(): void
    {
        $user = User::factory()->create([
            'profile_photo_path' => 'profiles/test.jpg',
        ]);

        $url = $user->profile_photo_url;

        $this->assertStringContainsString('profiles/test.jpg', $url);
    }

    #[Test]
    public function uses_soft_deletes(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted($user);
    }
}
