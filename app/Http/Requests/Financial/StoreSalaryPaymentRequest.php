<?php

namespace App\Http\Requests\Financial;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Financial->value) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'          => ['required', 'integer', 'exists:users,id'],
            'base_amount'      => ['nullable', 'numeric', 'min:0.01'],
            'deduction_amount' => ['nullable', 'numeric', 'min:0'],
            'bonus_amount'     => ['nullable', 'numeric', 'min:0'],
            'pay_period_start' => ['required', 'date'],
            'pay_period_end'   => ['required', 'date', 'after_or_equal:pay_period_start'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ];
    }
}
