<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Financial;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function index_returns_403_for_non_financial(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($user)->get('/financial/dashboard');

        $response->assertStatus(403);
    }

    #[Test]
    public function index_displays_tealth_for_financial(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');
        \App\Models\Financial::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/financial/dashboard');

        $response->assertStatus(200);
    }
}
