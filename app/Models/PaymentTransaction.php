<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'invoice_id',
        'transaction_id',
        'payment_method',
        'amount',
        'currency',
        'status',
        'gateway_response',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'gateway_response' => 'array', // Auto converts JSON payloads from Visa/PalPay into PHP Arrays
    ];

    /**
     * Get the invoice that this electronic payment belongs to.
     */
    public function invoice(): BelongsTo
    {
        return
            $this->belongsTo(Invoice::class);
    }
}
