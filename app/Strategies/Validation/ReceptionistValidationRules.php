<?php

declare(strict_types=1);

namespace App\Strategies\Validation;

use App\Contracts\Validation\RoleValidationRulesInterface;
use App\Models\User;
use Illuminate\Validation\Rule;

class ReceptionistValidationRules implements RoleValidationRulesInterface
{
    /**
     * Get the validation rules specific to the receptionist role for registration.
     *
     * @return array
     */
    public function getRegistrationRules(): array
    {
        $coreRules = CoreUserRules::getRegistrationRules();

        $roleRules = [
            'role'            => ['required', 'string', 'in:receptionist'],
            'department_id'   => ['required', 'exists:departments,id'],
            'employee_number' => ['required', 'string', Rule::unique('receptionists', 'employee_number')],
            'hiring_date'     => ['required', 'date', 'before_or_equal:today'],
        ];

        return array_merge($coreRules, $roleRules);
    }

    /**
     * Get the validation rules specific to the receptionist role for profile update.
     *
     * @param User $user The authenticated user model to safely ignore unique constraints.
     * @param array $input
     * @return array
     */
    public function getUpdateRules(User $user, array $input): array
    {
        return array_merge(
            CoreUserRules::getUpdateRules($user->id),
            [
                'role'            => ['required', 'string', 'in:receptionist'],

                'department_id'   => ['required', 'exists:departments,id'],
                'employee_number' => ['required', 'string', Rule::unique('receptionists', 'employee_number')->ignore($user->receptionist?->id)],
                'hiring_date'     => ['required', 'date', 'before_or_equal:today'],
            ]
        );
    }

    /**
     * Get custom messages for receptionist validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        $coreMessage = CoreUserRules::getRegistrationMessages();

        $roleMessage = [
            'department_id.required'   => 'The corporate department allocation is required for receptionist placement.',
            'department_id.exists'     => 'The selected corporate department is invalid or unregistered.',
            'employee_number.required' => 'The official institutional employee identification number is strictly required.',
            'employee_number.unique'   => 'This employee identification number is already assigned to another staff member.',
            'hiring_date.required'     => 'The official contract hiring date is required for personnel records.',
            'hiring_date.date'         => 'The hiring date must be a valid date representation.',
            'hiring_date.before_or_equal' => 'The hiring date cannot be designated in a future calendar period.',
        ];
        return array_merge($coreMessage, $roleMessage);
    }
}
