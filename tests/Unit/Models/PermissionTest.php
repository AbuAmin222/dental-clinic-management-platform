<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new Permission())->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('display_name', $fillable);
        $this->assertContains('group', $fillable);
    }

    #[Test]
    public function roles_relationship(): void
    {
        $permission = Permission::factory()->create();
        $role = Role::where('name', 'admin')->first();
        $role->givePermissionTo($permission);

        $this->assertCount(1, $permission->roles);
        $this->assertInstanceOf(Role::class, $permission->roles->first());
    }
}
