<?php

declare(strict_types=1);

namespace App\Contracts\Profile;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface RoleProfileStrategyInterface
{
    /**
     * Create a new role-specific profile for the user.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return void
     */
    public function create(User $user, array $data): void;

    /**
     * Update the existing role-specific profile for the user.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return void
     */
    public function update(User $user, array $data): void;

    /**
     * Retrieve the specific profile model associated with the user.
     *
     * @param User $user
     * @return Model|null
     */
    public function getProfile(User $user): ?Model;

    /**
     * Delete the specific profile associated with the user.
     *
     * @param User $user
     * @return void
     */
    public function delete(User $user): void;
}
