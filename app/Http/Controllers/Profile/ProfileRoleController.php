<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class ProfileRoleController
 *
 * Orchestrates polymorphic user role profile updates by delegating complex
 * strategy resolution and atomic persistence to the UserService layer.
 *
 * @package App\Http\Controllers\Profile
 */
class ProfileRoleController extends Controller
{
    /**
     * Inject UserService via Constructor Injection.
     */
    public function __construct(
        protected readonly UserService $userService
    ) {}

    /**
     * Update polymorphic user role metadata through domain service layer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->back()->with('error', __('Unauthenticated request.'));
        }

        $this->userService->updateUserProfile($user, $request->all());

        return redirect()->back()->with('success', __('Professional clinical metadata synchronized perfectly.'));
    }
}
