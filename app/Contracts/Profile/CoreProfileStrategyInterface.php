<?php

declare(strict_types=1);

namespace App\Contracts\Profile;

use App\Models\User;

interface CoreProfileStrategyInterface
{
    /**
     * Create a new core User record.
     *
     * @param array<string, mixed> $data
     * @param string|null $profilePath
     * @param string|null $identityPath
     * @return User
     */
    public function create(array $data, ?string $profilePath = null, ?string $identityPath = null): User;

    /**
     * Update base User profile record.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @param string|null $profilePath
     * @param string|null $identityPath
     * @return User
     */
    public function update(User $user, array $data, ?string $profilePath = null, ?string $identityPath = null): User;

    /**
     * Delete the core user model record.
     *
     * @param User $user
     * @return void
     */
    public function delete(User $user): void;
}
