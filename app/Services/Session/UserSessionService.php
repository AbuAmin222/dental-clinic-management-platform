<?php

declare(strict_types=1);

namespace App\Services\Session;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Agent;

/**
 * Class UserSessionService
 *
 * Domain Service responsible for querying authenticated session pipelines and parsing user-agent telemetry.
 *
 * @package App\Services
 */
class UserSessionService
{
    /**
     * Retrieve and compile active storage sessions for a given user.
     *
     * @param  \App\Models\User  $user
     * @param  string  $currentSessionId
     * @return array<int, array<string, mixed>>
     */
    public function getActiveSessions(User $user, string $currentSessionId): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        return collect(
            DB::table('sessions')
                ->where('user_id', $user->getKey())
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(function (object $session) use ($currentSessionId): array {
            $agent = $this->createAgent($session);

            return [
                'id'                => $session->id,
                'ip_address'        => $session->ip_address,
                'is_current_device' => $session->id === $currentSessionId,
                'agent'             => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform'   => $agent->platform(),
                    'browser'    => $agent->browser(),
                ],
                'last_active'       => Carbon::createFromTimestamp((int) $session->last_activity)->diffForHumans(),
            ];
        })->all();
    }

    /**
     * Instantiate and configure UserAgent agent instance.
     *
     * @param  object  $session
     * @return \Laravel\Jetstream\Agent
     */
    protected function createAgent(object $session): Agent
    {
        return tap(new Agent(), static function (Agent $agent) use ($session): void {
            $agent->setUserAgent((string) ($session->user_agent ?? ''));
        });
    }
}
