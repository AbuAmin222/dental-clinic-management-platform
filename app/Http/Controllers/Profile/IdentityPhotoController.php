<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Contracts\Storage\FileStorageServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IdentityPhotoController
 *
 * BUG FIX / MISSING FEATURE: identity documents are uploaded to the `local` disk (see
 * UserService — `secure/{role}/identities`, disk 'local') precisely because they're
 * sensitive and must never be web-accessible directly. But that also means there was
 * previously no route capable of serving one back at all — the Personal Profile edit
 * page had no way to display or confirm the currently-stored identity photo, matching
 * the reported "cannot show identity photo" symptom. This mirrors the existing secure
 * streaming pattern already used for dental X-rays (DentalRecordImageController).
 *
 * @package App\Http\Controllers\Profile
 */
class IdentityPhotoController extends Controller
{
    public function __construct(
        protected readonly FileStorageServiceInterface $storageService
    ) {}

    /**
     * Stream a user's identity document securely to its owner or an admin.
     *
     * Defaults to the currently authenticated user when no {user} is given (the
     * self-service "view my own identity photo" case). Reuses UserPolicy::view() — the
     * same authorization rule already governing whether a person may view a user's
     * account (admin, or the account's own owner) — for the "view someone else's"
     * (admin) case.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User|null  $user  The account whose identity photo is requested.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, ?User $user = null): Response
    {
        $user ??= $request->user();

        abort_if($user === null, 404);

        $this->authorize('view', $user);

        $path = $user->identity_photo_path;

        abort_if(
            empty($path) || !$this->storageService->exists((string) $path, 'local'),
            404,
            'No identity document has been uploaded for this account.'
        );

        return $this->storageService->response((string) $path, 'local');
    }
}
