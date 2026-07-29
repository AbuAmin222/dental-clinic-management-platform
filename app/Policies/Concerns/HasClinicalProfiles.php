<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;

trait HasClinicalProfiles
{
    /**
     * Request-level static cache to prevent redundant database queries (N+1 safety).
     *
     * @var array<string, array<int, int|null>>
     */
    protected static array $profileIdCache = [];

    /**
     * Intercept all authorization checks to strictly block inactive users.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): ?bool
    {
        if (!$user->is_active) {
            return false;
        }

        return null; // Fallback to concrete policy methods
    }

    /**
     * Securely resolve and memoize Doctor Profile ID.
     *
     * @param  \App\Models\User  $user
     * @return int|null
     */
    protected function getDoctorId(User $user): ?int
    {
        if ($user->role !== 'doctor') {
            return null;
        }

        $userId = $user->id;
        if (!isset(self::$profileIdCache['doctor'][$userId])) {
            self::$profileIdCache['doctor'][$userId] = $user->doctor?->id;
        }

        return self::$profileIdCache['doctor'][$userId];
    }

    /**
     * Securely resolve and memoize Patient Profile ID.
     *
     * @param  \App\Models\User  $user
     * @return int|null
     */
    protected function getPatientId(User $user): ?int
    {
        if ($user->role !== 'patient') {
            return null;
        }

        $userId = $user->id;
        if (!isset(self::$profileIdCache['patient'][$userId])) {
            self::$profileIdCache['patient'][$userId] = $user->patient?->id;
        }

        return self::$profileIdCache['patient'][$userId];
    }

    /**
     * Securely resolve and memoize Receptionist Profile ID.
     *
     * @param  \App\Models\User  $user
     * @return int|null
     */
    protected function getReceptionistId(User $user): ?int
    {
        if ($user->role !== 'receptionist') {
            return null;
        }

        $userId = $user->id;
        if (!isset(self::$profileIdCache['receptionist'][$userId])) {
            self::$profileIdCache['receptionist'][$userId] = $user->receptionist?->id;
        }

        return self::$profileIdCache['receptionist'][$userId];
    }
}
