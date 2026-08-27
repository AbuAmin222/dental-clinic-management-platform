<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_id',
        'title',
        'bank_phone_number',
        'visa_card_number',
        'account_number',
        'iban',
        'qr_code_path',
        'is_visible_to_patient',
        'is_active',
    ];

    protected $hidden = [
        'visa_card_number',
    ];

    protected $casts = [
        'visa_card_number'         => 'encrypted',
        'is_visible_to_patient'    => 'boolean',
        'is_active'                 => 'boolean',
    ];

    public function financial(): BelongsTo
    {
        return $this->belongsTo(Financial::class);
    }

    /**
     * Scope: methods safe to expose in a patient-facing payment screen.
     */
    public function scopeForPatientDisplay(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_visible_to_patient', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Masked card number (last 4 digits only) for any display context outside the
     * financial-officer management screens.
     */
    protected function maskedVisaNumber(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->visa_card_number) {
                    return null;
                }

                $digits = preg_replace('/\D/', '', $this->visa_card_number);

                return '**** **** **** ' . substr($digits, -4);
            }
        );
    }
}
