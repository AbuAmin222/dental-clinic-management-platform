<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    #[Test]
    public function index_returns_all_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->getJson('/api/admin/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    #[Test]
    public function store_creates_new_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->postJson('/api/admin/permissions', [
            'name' => 'custom.permission',
            'display_name' => 'Custom Permission',
            'group' => 'custom',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas('permissions', ['name' => 'custom.permission']);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->postJson('/api/admin/permissions', []);

        $response->assertStatus(422);
    }

    #[Test]
    public function index_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($user)->getJson('/api/admin/permissions');

        $response->assertStatus(403);
    }
}
