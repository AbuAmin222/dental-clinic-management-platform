<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Invoice;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HasClinicalProfilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function before_blocks_inactive_users(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $user->assignRole('admin');

        $policy = new class {
            use HasClinicalProfiles;
        };

        $this->assertFalse($policy->before($user, 'view'));
    }

    #[Test]
    public function before_grants_admin_unconditional_access(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $policy = new class {
            use HasClinicalProfiles;
        };

        $this->assertTrue($policy->before($user, 'view'));
    }

    #[Test]
    public function before_returns_null_for_non_admin_active_user(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('doctor');

        $policy = new class {
            use HasClinicalProfiles;
        };

        $this->assertNull($policy->before($user, 'view'));
    }
}
