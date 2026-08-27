<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin->value) ?? false;
    }

    public function rules(): array
    {
        return [
            'role'       => ['required', 'string', Rule::in(UserRole::values())],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
