<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Session\UserSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserSessionServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserSessionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserSessionService();
    }

    #[Test]
    public function get_active_sessions_returns_empty_array_when_session_driver_is_not_database(): void
    {
        config(['session.driver' => 'array']);

        $user = User::factory()->create();

        $result = $this->service->getActiveSessions($user, 'current-session-id');

        $this->assertSame([], $result);
    }

    #[Test]
    public function get_active_sessions_returns_empty_array_when_no_sessions_exist(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create();

        $result = $this->service->getActiveSessions($user, 'current-session-id');

        $this->assertSame([], $result);
    }
}
