<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Laravel\Jetstream\Agent;

class UserProfileController extends Controller
{
    public function edit(Request $request)
    {
        return Inertia::render('Profile/EditProfile');
    }

    public function password(Request $request)
    {
        return Inertia::render('Profile/ManagePassword');
    }

    public function twoFactor(Request $request)
    {
        return Inertia::render('Profile/TwoFactorAuth');
    }

    public function devices(Request $request)
    {
        return Inertia::render('Profile/ManageDevices', [
            'sessions' => $this->getSessions($request)
        ]);
    }

    public function deleteAccount(Request $request)
    {
        return Inertia::render('Profile/DeleteAccount');
    }

    protected function getSessions(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return [];
        }
        return collect(DB::table('sessions')
            ->where('user_id', $request->user()->getKey())
            ->orderBy('last_activity', 'desc')
            ->get())->map(function ($session) use ($request) {
            $agent = $this->createAgent($session);
            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'last_active' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        })->all();
    }

    protected function createAgent($session)
    {
        return tap(new Agent, function ($agent) use ($session) {
            $agent->setUserAgent($session->user_agent);
        });
    }
}
