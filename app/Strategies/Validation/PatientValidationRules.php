<?php

declare(strict_types=1);

namespace App\Strategies\Validation;

use App\Contracts\Validation\RoleValidationRulesInterface;
use App\Models\User;
use Illuminate\Validation\Rule;

class PatientValidationRules implements RoleValidationRulesInterface
{
    /**
     * Get registration rules by merging core user rules with patient specific rules.
     */
    public function getRegistrationRules(): array
    {
        return array_merge(
            CoreUserRules::getRegistrationRules(),
            [
                'role'              => ['required', 'string', 'in:patient'],

                'blood_group'             => ['required', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],

                'allergies'               => ['nullable', 'string'],
                'chronic_diseases'        => ['nullable', 'string'],
                'medical_notes'           => ['nullable', 'string'],

                'emergency_contact_name'  => ['required', 'string', 'min:3', 'max:30'],
                'emergency_contact_phone' => ['required', 'regex:/^(059|056)\d{7}$/'],
            ]
        );
    }

    /**
     * Get update rules by safely passing the User model to ignore unique rules.
     */
    public function getUpdateRules(User $user, array $input): array
    {
        return array_merge(
            CoreUserRules::getUpdateRules($user->id),
            [
                'role'              => ['required', 'string', 'in:patient'],

                'blood_group'             => ['required', 'string', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],

                'allergies'               => ['nullable', 'string'],
                'chronic_diseases'        => ['nullable', 'string'],
                'medical_notes'           => ['nullable', 'string'],

                'emergency_contact_name'  => ['required', 'string', 'min:3', 'max:30'],
                'emergency_contact_phone' => ['required', 'regex:/^(059|056)\d{7}$/'],
            ]
        );
    }

    /**
     * Get custom messages for patient validation.
     */
    public function messages(): array
    {
        $coreMessage = CoreUserRules::getRegistrationMessages();

        $roleMessage = [
            'blood_group.required'             => 'Blood type is a mandatory field for the patient to facilitate emergency cases.',
            'blood_group.in'                   => 'The specific blood type does not follow standard global classifications.',
            'emergency_contact_name.required'  => 'The name of the person to contact in case of emergency is required.',
            'emergency_contact_phone.required' => 'An emergency phone number is required to protect the patient.',
            'emergency_contact_phone.regex' => 'The emergency contact phone must start with 059 (Jawwal) or 056 (Ooredoo)followed by 7 digits.',

        ];
        return array_merge($coreMessage, $roleMessage);
    }
}
