<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_id',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'due_date',
    ];
    protected $casts = [
        'total_amount'   => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'due_date'       => 'datetime',
    ];

    public function doctor(): BelongsTo
    {
        return
            $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return
            $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return
            $this->belongsTo(Appointment::class);
    }

    /**
     * Get all payment gateway transactions linked to this invoice.
     */
    public function paymentTransactions(): HasMany
    {
        return
            $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Dynamic business logic helper to trigger a secure payment increment.
     */
    public function pay($amount)
    {
        $this->paid_amount += $amount;

        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        }

        return
            $this->save();
    }

    /**
     * Automated Model Observers for extreme data integrity.
     */
    protected static function booted(): void
    {
        static::saving(function ($invoice) {
            // Auto calculate the safe remaining balance natively before saving to DB
            $invoice->balance_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
        });
    }
}
