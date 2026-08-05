<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Profile\CoreProfileStrategyInterface;
use App\Factories\Profile\RoleProfileFactory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

use function App\Helpers\storage_engine;

// Make photo Name and submit it to upload and update.
// Make Core data operations(crud).
class UserService
{
    public function __construct(
        private readonly CoreProfileStrategyInterface $coreProfileStrategy
    ) {}
    /**
     * Execute a secure transactional procedure to create a base user and their dedicated profile.
     *
     * @param  array<string, mixed>  $data  Complete pre-validated array of parameters.
     * @return \App\Models\User              The persisted fully configured User model instance.
     *
     * @throws \Exception If database persistence or file uploads encounter an unhandled exception.
     */
    public function registerUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $fullName = $data['first_name'] . ' ' . $data['last_name'];
            $roleDir = strtolower($data['role']);

            $profilePhotoFile = $data['profile_photo'] ?? null;
            $identityPhotoFile = $data['identity_photo'] ?? null;

            $profilePath = null;
            $identityPath = null;

            if (isset($profilePhotoFile) && $profilePhotoFile instanceof UploadedFile) {
                $profilePath = storage_engine()->upload($fullName, $profilePhotoFile, "uploads/{$roleDir}/profiles");
            }

            if (isset($identityPhotoFile) && $identityPhotoFile instanceof UploadedFile) {
                $identityPath = storage_engine()->upload($fullName, $identityPhotoFile, "secure/{$roleDir}/identities", 'local');
            }

            $user = $this->coreProfileStrategy->create($data, $profilePath, $identityPath);

            RoleProfileFactory::make($roleDir)->create($user, $data);

            return $user;
        });
    }

    /**
     * Process core and role-based updates for an existing user account profile securely.
     *
     * @param  \App\Models\User      $user  The model instance targeted for modification.
     * @param  array<string, mixed>  $data  The dynamic set of updated payloads.
     * @return \App\Models\User             The refreshed and updated User instance.
     *
     * @throws \Exception If updating assets or transactional queries fail.
     */
    public function updateUserProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $fullName = $data['first_name'] . ' ' . $data['last_name'];
            $roleDir = strtolower($user->role);

            $profilePhotoFile = $data['profile_photo'] ?? null;
            $identityPhotoFile = $data['identity_photo'] ?? null;

            $profilePath = null;
            $identityPath = null;

            if (isset($profilePhotoFile) && $profilePhotoFile instanceof UploadedFile) {
                $profilePath = storage_engine()->update($fullName, $profilePhotoFile, $user->profile_photo_path, "uploads/{$roleDir}/profiles");
            }

            if (isset($identityPhotoFile) && $identityPhotoFile instanceof UploadedFile) {
                $identityPath = storage_engine()->update($fullName, $identityPhotoFile, $user->identity_photo_path, "secure/{$roleDir}/identities", 'local');
            }

            $user = $this->coreProfileStrategy->update($user, $data, $profilePath, $identityPath);

            RoleProfileFactory::make($user->role)->update($user, $data);

            return $user;
        });
    }

    /**
     * Safely delete a user, their concrete role-based profile, and clear all related storage assets.
     *
     * @param  \App\Models\User  $user  The user instance targeted for complete deletion.
     * @return void
     *
     * @throws \Exception If the deletion transaction or structural unlinking fails.
     */
    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->tokens()->delete();

            RoleProfileFactory::make($user->role)->delete($user);

            if ($user->profile_photo_path) {
                storage_engine()->delete($user->profile_photo_path);
            }

            if ($user->identity_photo_path) {
                storage_engine()->delete($user->identity_photo_path, 'local');
            }

            $this->coreProfileStrategy->delete($user);
        });
    }

    /**
     * Check if the provided email address is available for registration.
     */
    public function isEmailAvailable(string $email, ?int $ignoreUserId = null): bool
    {
        return ! User::query()
            ->where('email', $email)
            ->when($ignoreUserId, fn($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists();
    }

    /**
     * Check if the provided username is available for registration.
     */
    public function isUsernameAvailable(string $username, ?int $ignoreUserId = null): bool
    {
        return ! User::query()
            ->where('username', $username)
            ->when($ignoreUserId, fn($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists();
    }
}
