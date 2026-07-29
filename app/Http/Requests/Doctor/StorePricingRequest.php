<?php

declare(strict_types=1);

namespace App\Http\Requests\Doctor;

use App\Models\Pricing;
use Illuminate\Foundation\Http\FormRequest;

class StorePricingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('create', Pricing::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'service_name' => ['required', 'string', 'min:3', 'max:150'],
            'amount'       => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_name.required' => __('The service name is required.'),
            'service_name.min'      => __('The service name must be at least 3 characters.'),
            'service_name.max'      => __('The service name must not exceed 150 characters.'),
            'amount.required'       => __('Pricing amount is required.'),
            'amount.numeric'        => __('Pricing amount must be a valid monetary number.'),
            'amount.min'            => __('Pricing amount cannot be negative.'),
            'amount.max'            => __('Pricing amount exceeds the maximum allowed limit.'),
        ];
    }
}
