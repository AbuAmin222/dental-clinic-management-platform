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

    public function registerCoreData(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $fullName = $data['first_name'] . ' ' . $data['last_name'];
            $roleDir = strtolower($data['role']);

            $profilePhotoFile = $data['profile_photo'] ?? null;
            $identityPhotoFile = $data['identity_photo'] ?? null;

            $profilePath = $this->storePhoto($profilePhotoFile, $roleDir, $fullName, 'profiles');

            $identityPath = $this->storePhoto($identityPhotoFile, $roleDir, $fullName, 'identities', 'secure', 'local');

            $user = $this->coreProfileStrategy->create($data, $profilePath, $identityPath);

            return $user;
        });
    }

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
     * Update ONLY the core `users` row for an existing account — no role profile touch.
     *
     * @param  \App\Models\User      $user  The user whose core identity data is being updated.
     * @param  array<string, mixed>  $data  Pre-validated core fields (+ optional identity_photo/profile_photo uploads).
     * @return \App\Models\User
     */
    public function updateCoreProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $fullName = $data['first_name'] . ' ' . $data['last_name'];
            $roleDir = strtolower($user->role ?? 'user');

            $profilePhotoFile = $data['photo'] ?? $data['profile_photo'] ?? null;
            $identityPhotoFile = $data['identity_photo'] ?? null;

            $profilePath = null;
            $identityPath = null;

            if (isset($profilePhotoFile) && $profilePhotoFile instanceof UploadedFile) {
                $profilePath = storage_engine()->update($fullName, $profilePhotoFile, $user->profile_photo_path, "uploads/{$roleDir}/profiles");
            }

            if (isset($identityPhotoFile) && $identityPhotoFile instanceof UploadedFile) {
                $identityPath = storage_engine()->update($fullName, $identityPhotoFile, $user->identity_photo_path, "secure/{$roleDir}/identities", 'local');
            }

            return $this->coreProfileStrategy->update($user, $data, $profilePath, $identityPath);
        });
    }

    /**
     * Update ONLY the role-specific profile record for an existing user (Doctor,
     * Patient, Receptionist, Financial, ...) — leaves the core `users` row untouched.
     *
     * BUG FIX: ProfileRoleController previously called updateUserProfile() above for
     * role-only saves. That method unconditionally also runs
     * `$this->coreProfileStrategy->update($user, $data, ...)`, which — for a role-only
     * payload — reads `$data['profile_photo'] ?? null` / `$data['identity_photo'] ?? null`
     * (never present in a role-details submission) and always resolves to null, then
     * falls back to the user's existing photo paths. That fallback path means no data
     * is actually lost, but it's still doing real, unnecessary work (a second query +
     * write to the `users` table) on every role save, and conflates two operations the
     * UI already presents as fully independent ("Save Structural Changes" vs
     * "Save Role Details"). This method gives ProfileRoleController a properly scoped
     * write path that touches only what the role-details form actually submitted.
     *
     * @param  \App\Models\User      $user  The user whose role profile is being updated.
     * @param  array<string, mixed>  $data  Pre-validated role-specific fields only.
     * @return void
     */
    public function updateRoleProfile(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            RoleProfileFactory::make($user->role)->update($user, $data);
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

            $profilePhotoFile = $data['photo'] ?? $data['profile_photo'] ?? null;
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

            $this->delPhoto($user, 'profile_photo_path');
            $this->delPhoto($user, 'identity_photo_path', 'local');

            $this->coreProfileStrategy->delete($user);
        });
    }

    private function storePhoto(UploadedFile $photoFile, string $roleDir, string $name, string $supDir, ?string $mainDir = 'uploads', ?string $disk = 'public'): string
    {
        $photoPath = null;

        if (isset($photoFile) && $photoFile instanceof UploadedFile) {
            $photoPath = storage_engine()->upload($name, $photoFile, "{$mainDir}/{$roleDir}/{$supDir}", "{$disk}");
        }
        return $photoPath;
    }

    private function delPhoto(User $user, string $photoFile, ?string $disk = 'public'): void
    {
        if ($user->$photoFile) {
            storage_engine()->delete($user->$photoFile, $disk);
        }
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
