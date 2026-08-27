<?php

declare(strict_types=1);

namespace App\Http\Requests\AccountSecurity;

use Illuminate\Foundation\Http\FormRequest;

class UpdateForcedPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:10', 'max:35', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => __('Your new password must be at least 10 characters long.'),
            'password.confirmed' => __('The password confirmation does not match.'),
        ];
    }
}
