<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Factories\Validation\RoleValidationFactory;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = $this->input('role', 'patient');
        $strategy = RoleValidationFactory::make($role);

        return $strategy->getRegistrationRules();
    }

    public function messages(): array
    {
        $role = $this->input('role', 'patient');
        $strategy = RoleValidationFactory::make($role);

        return $strategy->messages();
    }
}
