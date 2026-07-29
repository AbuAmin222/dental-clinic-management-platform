<?php

declare(strict_types=1);

namespace App\Contracts\Validation;

use App\Models\User;

interface RoleValidationRulesInterface
{
    /**
     * Get the validation rules specific to the role for registration.
     *
     * @return array
     */
    public function getRegistrationRules(): array;

    /**
     * Get the validation rules specific to the role for profile update.
     *
     * @param User $user The authenticated user model to safely ignore unique constraints.
     * @param array $input
     * @return array
     */
    public function getUpdateRules(User $user, array $input): array;

    /**
     * Get custom messages for the validation rules.
     *
     * @return array
     */
    public function messages(): array;
}
