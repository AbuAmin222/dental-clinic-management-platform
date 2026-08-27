<?php

declare(strict_types=1);

namespace App\Http\Requests\AccountSecurity;

use Illuminate\Foundation\Http\FormRequest;

class VerifyAccountCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:' . config('clinic.account_security.verification_code_length', 6)],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => __('Please enter the verification code sent to you.'),
            'code.size' => __('The verification code must be exactly :size digits.', ['size' => config('clinic.account_security.verification_code_length', 6)]),
        ];
    }
}
