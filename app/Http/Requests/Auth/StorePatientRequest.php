<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Factories\Validation\RoleValidationFactory;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StorePatientRequest
 *
 * Note: 'password' is intentionally excluded — the receptionist-initiated onboarding
 * flow assigns the system-generated password (= identity_number) inside
 * RegisterPatientAction, so it must not be collected or validated from this form.
 */
class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole(UserRole::Receptionist->value);
    }

    public function rules(): array
    {
        $rules = RoleValidationFactory::make('patient')->getRegistrationRules();

        unset($rules['password']);

        return $rules;
    }

    public function messages(): array
    {
        return RoleValidationFactory::make('patient')->messages();
    }
}
