<?php

declare(strict_types=1);

namespace App\Http\Requests\Receptionist;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreInvoiceRequest
 * Validates financial ledgers and points-of-sale operational payloads created by desks.
 */
class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Receptionist->value) ?? false;
    }

    public function rules(): array
    {
        return [
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.pricing_id'        => ['required', 'integer', 'exists:pricings,id'],
            'items.*.quantity'          => ['required', 'integer', 'min:1'],
            'due_date'                  => ['required', 'date'],
            'tax_amount'                => ['nullable', 'numeric', 'min:0'],
            'discount_amount'           => ['nullable', 'numeric', 'min:0'],
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
            'items.required'              => __('At least one priced service must be added to the invoice.'),
            'items.array'                 => __('The invoice items must be a valid list.'),
            'items.min'                   => __('At least one priced service must be added to the invoice.'),
            'items.*.pricing_id.required' => __('Please select a service from the pricing catalog for each item.'),
            'items.*.pricing_id.exists'   => __('One of the selected services no longer exists in the pricing catalog.'),
            'items.*.quantity.required'   => __('Please specify a quantity for each invoice item.'),
            'items.*.quantity.min'        => __('The quantity must be at least 1 for each invoice item.'),
            'due_date.required'           => __('The invoice due date is required.'),
            'due_date.date'               => __('The invoice due date must be a valid date.'),
            'tax_amount.numeric'          => __('The tax amount must be a valid number.'),
            'tax_amount.min'              => __('The tax amount cannot be negative.'),
            'discount_amount.numeric'     => __('The discount amount must be a valid number.'),
            'discount_amount.min'         => __('The discount amount cannot be negative.'),
        ];
    }
}
