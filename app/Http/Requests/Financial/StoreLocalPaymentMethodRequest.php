<?php

declare(strict_types=1);

namespace App\Http\Requests\Financial;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLocalPaymentMethodRequest extends FormRequest
{
    private const CONTACT_FIELDS = [
        'bank_phone_number',
        'visa_card_number',
        'account_number',
        'iban',
        'qr_code_path',
    ];

    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Financial->value) ?? false;
    }

    public function rules(): array
    {
        return [
            'title'                     => ['required', 'string', 'max:255'],
            'bank_phone_number'         => ['nullable', 'string', 'max:20'],
            'visa_card_number'          => ['nullable', 'string', 'max:32'],
            'account_number'            => ['nullable', 'string', 'max:64'],
            'iban'                      => ['nullable', 'string', 'max:34'],
            'qr_code_path'              => ['nullable', 'string', 'max:2048'],
            'is_visible_to_patient'     => ['boolean'],
            'is_active'                 => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasAnyContactField = collect(self::CONTACT_FIELDS)
                ->some(fn(string $field): bool => filled($this->input($field)));

            if (! $hasAnyContactField) {
                $validator->errors()->add(
                    'bank_phone_number',
                    __('At least one payment contact field must be provided (phone, card, account, IBAN, or QR code).')
                );
            }
        });
    }
}
