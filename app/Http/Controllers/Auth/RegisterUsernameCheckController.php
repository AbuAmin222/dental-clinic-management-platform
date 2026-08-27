<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RegisterUsernameCheckController
 *
 * REFACTOR: this logic previously lived as `checkUsername()` on
 * `Receptionist\PatientController`, even though the route it serves
 * (`POST /check-username`) is completely public and unauthenticated — registered under
 * `routes/roles/auth.php`, alongside RegisterEmailCheckController, not under any
 * `role:receptionist` group. Housing a publicly-reachable, registration-time
 * availability check inside a controller class named and namespaced for
 * receptionist-only patient management was a cohesion break: nothing about this action
 * is receptionist-specific, and its real sibling (the equivalent email-availability
 * check) already lives correctly in this Auth namespace. Moved here to match.
 *
 * Behavior is unchanged — same validation rule, same response shape — this is a pure
 * relocation, not a logic change.
 *
 * @package App\Http\Controllers\Auth
 */
class RegisterUsernameCheckController extends Controller
{
    /**
     * Check whether a username is available for registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:25'],
        ]);

        $isAllocated = User::where('username', $validated['username'])->exists();

        return new JsonResponse([
            'valid'   => !$isAllocated,
            'message' => $isAllocated
                ? __('System identifier already claimed.')
                : __('Identifier available for assignment.'),
        ], Response::HTTP_OK);
    }
}
