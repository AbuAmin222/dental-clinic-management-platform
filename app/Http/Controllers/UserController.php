<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 *
 * Manages administrative API lifecycle operations for User entities.
 * Delegates core persistence logic to UserService while enforcing explicit Policy authorization.
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * Inject UserService layer.
     */
    public function __construct(
        protected readonly UserService $userService
    ) {}

    /**
     * Create a user account and its associated polymorphic profile.
     *
     * @param  \App\Http\Requests\StoreUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->registerUser($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('The medical account has been created and permissions have been successfully activated.'),
            'data'    => [
                'id'        => $user->id,
                'full_name' => $user->full_name,
                'role'      => $user->role,
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Update user credentials and role-specific profile records.
     *
     * @param  \App\Http\Requests\UpdateUserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $updatedUser = $this->userService->updateUserProfile($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('The profile and all associated records have been successfully updated.'),
            'data'    => [
                'id'        => $updatedUser->id,
                'full_name' => $updatedUser->full_name,
            ]
        ], Response::HTTP_OK);
    }

    /**
     * Safely delete the user account and purge physical assets.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->userService->deleteUser($user);

        return response()->json([
            'success' => true,
            'message' => __('The account deletion and associated physical assets were safely processed.')
        ], Response::HTTP_OK);
    }
}
