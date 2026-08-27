<?php

declare(strict_types=1);

namespace App\Strategies\Validation;

use App\Contracts\Validation\RoleValidationRulesInterface;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * NOTE: `financial` is intentionally NOT one of the public self-registration roles
 * (architecture document §2.b — a financial officer registers as a Guest, then an Admin
 * promotes the account's role). getRegistrationRules() therefore validates the profile
 * *completion* form fields the officer fills in themselves after promotion, not a public
 * sign-up form; RoleValidationFactory still resolves it the same way as every other role
 * for architectural consistency.
 */
class FinancialValidationRules implements RoleValidationRulesInterface
{
    public function getRegistrationRules(): array
    {
        return [
            'employee_number'  => ['required', 'string', Rule::unique('financials', 'employee_number')],
            'hiring_date'      => ['nullable', 'date', 'before_or_equal:today'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'specialization'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function getUpdateRules(User $user, array $input): array
    {
        return [
            'employee_number'  => ['required', 'string', Rule::unique('financials', 'employee_number')->ignore($user->financial?->id)],
            'hiring_date'      => ['nullable', 'date', 'before_or_equal:today'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'specialization'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_number.required' => 'The official institutional employee identification number is required.',
            'employee_number.unique'   => 'This employee identification number is already assigned to another financial officer.',
            'hiring_date.before_or_equal' => 'The hiring date cannot be in the future.',
        ];
    }
}
