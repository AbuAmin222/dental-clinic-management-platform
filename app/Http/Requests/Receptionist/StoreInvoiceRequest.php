<?php

declare(strict_types=1);

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreInvoiceRequest
 * Validates financial ledgers and points-of-sale operational payloads created by desks.
 */
class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'receptionist';
    }

    public function rules(): array
    {
        return [
            'total_amount' => ['required', 'numeric', 'min:0'],
            'paid_amount'  => ['required', 'numeric', 'min:0', 'max:' . ($this->input('total_amount') ?? 0)],
            'status'       => ['required', 'string', Rule::in(['paid', 'unpaid', 'partially_paid'])],
            'due_date'     => ['required', 'date'],
        ];
    }
}
