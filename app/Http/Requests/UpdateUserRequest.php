<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Factories\Validation\RoleValidationFactory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User $targetUser */
        $targetUser = $this->route('user') ?? $this->user();
        $strategy = RoleValidationFactory::make($targetUser->role);

        return $strategy->getUpdateRules($targetUser, $this->all());
    }

    public function messages(): array
    {
        /** @var User $targetUser */
        $targetUser = $this->route('user') ?? $this->user();
        $strategy = RoleValidationFactory::make($targetUser->role);

        return $strategy->messages();
    }
}
