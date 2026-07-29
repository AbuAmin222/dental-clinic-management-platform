<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use App\Services\UserService;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * The unified user service layer orchestrator instance.
     *
     * @var \App\Services\UserService
     */
    protected UserService $userService;

    /**
     * Construct the Jetstream action and inject the central user service.
     *
     * @param  \App\Services\UserService  $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Delete the given user along with their API tokens, polymorphic role profiles, and disk assets.
     *
     * @param  \App\Models\User  $user  The target user model instance to be completely wiped out.
     * @return void
     */
    public function delete(User $user): void
    {
        // 1. Clear out Jetstream API tokens if the feature is enabled in the application
        $user->tokens->each->delete();

        // 2. Delegate the cascading profile deletion and storage cleanups to the central service layer
        $this->userService->deleteUser($user);
    }
}
