<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Factories\Validation\RoleValidationFactory;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        $roleStrategy = RoleValidationFactory::make($user->role);

        $validated = Validator::make(
            $request->all(),
            $roleStrategy->getUpdateRules($user, $request->all()),
            $roleStrategy->messages()
        )->validate();

        // $this->userService->updateUserProfile($user, $validated);
        $this->userService->updateRoleProfile($user, $validated);


        return redirect()->back()->with('success', __('Professional clinical metadata synchronized perfectly.'));
    }
}
