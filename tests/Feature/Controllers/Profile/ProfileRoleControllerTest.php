<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function update_redirects_when_unauthenticated(): void
    {
        $response = $this->put('/user/profile-role', []);

        $response->assertSessionHasErrors();
    }
}
