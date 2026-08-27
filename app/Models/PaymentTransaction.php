<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'invoice_id',
        'local_payment_method_id',
        'transaction_id',
        'transaction_reference',
        'payment_method',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'proof_image_path',
        'notes',
    ];

    protected $casts = [
        'amount'           => MoneyCast::class,
        'gateway_response' => 'array', // Auto converts JSON payloads from Visa/PalPay into PHP Arrays
        'status'           => PaymentTransactionStatus::class,
        'payment_method'   => PaymentMethod::class,
    ];

    /**
     * Get the invoice that this payment belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Present only for local (manual proof-of-payment) transactions; null for
     * global gateway transactions (Visa/PayPal).
     */
    public function localPaymentMethod(): BelongsTo
    {
        return $this->belongsTo(LocalPaymentMethod::class);
    }
}
