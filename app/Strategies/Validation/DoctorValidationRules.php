<?php

declare(strict_types=1);

namespace App\Strategies\Validation;

use App\Contracts\Validation\RoleValidationRulesInterface;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\Rule;

class DoctorValidationRules implements RoleValidationRulesInterface
{
    /**
     * Get the validation rules specific to the doctor role for registration.
     *
     * @return array
     */
    public function getRegistrationRules(): array
    {
        $coreRules = CoreUserRules::getRegistrationRules();

        $roleRules = [
            'role'              => ['required', 'string', Rule::in([UserRole::Doctor->value])],
            'specialization_id' => ['required', 'exists:specializations,id'],
            'license_number'    => ['required', 'string', Rule::unique('doctors', 'license_number')],
            'experience_years'  => ['required', 'integer', 'min:0'],
            'bio'               => ['nullable', 'string', 'max:1000'],
        ];

        return array_merge($coreRules, $roleRules);
    }

    /**
     * Get the validation rules specific to the doctor role for profile update.
     *
     * @param User $user The authenticated user model to safely ignore unique constraints.
     * @param array $input
     * @return array
     */
    public function getUpdateRules(User $user, array $input): array
    {
        return [
            'specialization_id' => ['required', 'exists:specializations,id'],
            'license_number'    => ['required', 'string', Rule::unique('doctors', 'license_number')->ignore($user->doctor?->id)],
            'experience_years'  => ['required', 'integer', 'min:0', 'max:80'],
            'bio'               => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for doctor validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        $coreMessage = CoreUserRules::getRegistrationMessages();

        $roleMessage = [
            'specialization_id.required' => 'The clinical specialization field is mandatory for medical profile definition.',
            'specialization_id.exists'   => 'The selected clinical specialization does not exist in our institutional directories.',
            'license_number.required'    => 'The official medical practice license number is strictly required.',
            'license_number.unique'      => 'This medical practice license number is already registered under another physician profile.',
            'experience_years.required'  => 'The total years of clinical experience must be specified.',
            'experience_years.integer'   => 'The experience years must be a valid non-negative integer.',
            'experience_years.min'       => 'The experience years cannot be less than zero.',
            'experience_years.max'       => 'The experience years exceeds the realistic standard lifetime clinical practice window.',
            'bio.max'                    => 'The professional physician biography must not exceed 1000 characters.',
        ];

        return array_merge($coreMessage, $roleMessage);
    }
}
