<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Services\Session\UserSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class UserProfileController
 *
 * Core Identity Lifecycle Manager managing profile views, security credentials,
 * active telemetry devices, and account termination interfaces.
 * Delegates session analysis to UserSessionService adhering to SRP.
 *
 * @package App\Http\Controllers\Profile
 */
class UserProfileController extends Controller
{
    /**
     * Inject UserSessionService for clean separation of concerns.
     */
    public function __construct(
        protected readonly UserSessionService $sessionService
    ) {}

    public function edit(Request $request): InertiaResponse
    {
        return Inertia::render('Profile/EditProfile');
    }

    public function password(Request $request): InertiaResponse
    {
        return Inertia::render('Profile/ManagePassword');
    }

    public function twoFactor(Request $request): InertiaResponse
    {
        return Inertia::render('Profile/TwoFactorAuth');
    }

    public function devices(Request $request): InertiaResponse
    {
        $user = $request->user();

        return Inertia::render('Profile/ManageDevices', [
            'sessions' => $user ? $this->sessionService->getActiveSessions($user, $request->session()->getId()) : []
        ]);
    }

    public function deleteAccount(Request $request): InertiaResponse
    {
        return Inertia::render('Profile/DeleteAccount');
    }
}
