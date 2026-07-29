<?php

namespace App\Actions\Custom;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class DeleteUserAction
{
    /**
     * Core user service coordinator layer instance.
     * * @var \App\Services\UserService
     */
    protected UserService $userService;

    /**
     * Create the action class and inject structural service dependencies.
     *
     * @param  \App\Services\UserService  $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Authenticate permission scope and execute permanent deletion sequence for the target user.
     *
     * @param  \App\Models\User  $targetUser  The target user to be removed.
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException If current session does not possess execution authority.
     */
    public function execute(User $targetUser): void
    {
        // Example Security Policy Check: Ensure current user is Admin or authorized
        if (Auth::user()->role !== 'admin' && Auth::id() !== $targetUser->id) {
            throw new AuthorizationException('Unauthorized access: You do not have permission to delete this user profile.');
        }

        $this->userService->deleteUser($targetUser);
    }
}
//  كيف تستخدم دالة الحذف الجديدة الآن في أي مكان بالنظام؟
//  ببساطة، داخل أي Controller (سواء للوحة التحكم أو لـ API)، يمكنك حقن الـ Action واستدعاؤه كالتالي:
//  PHP
//  public function destroy(User $user, DeleteUserAction $deleteUserAction)
//  {
//      $deleteUserAction->execute($user);
//      return response()->json(['message' => 'User account deleted successfully.']);
//  }
