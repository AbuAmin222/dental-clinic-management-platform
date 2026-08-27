<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\InvoiceStatus;
use App\Exceptions\IllegalInvoiceStateTransitionException;
use App\States\Invoice\InvoiceState;
use App\States\Invoice\InvoiceStateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_id',
        'sub_total',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'balance_amount',
        'status',
        'due_date',
    ];

    protected $casts = [
        'sub_total'         => MoneyCast::class,
        'tax_amount'        => MoneyCast::class,
        'discount_amount'   => MoneyCast::class,
        'total_amount'      => MoneyCast::class,
        'paid_amount'       => MoneyCast::class,
        'due_amount'        => MoneyCast::class,
        'balance_amount'    => MoneyCast::class,
        'due_date'          => 'datetime',
        'status'            => InvoiceStatus::class,
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

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
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
     * Resolve the current State Pattern object for this invoice's status.
     */
    public function state(): InvoiceState
    {
        return InvoiceStateFactory::make($this->status);
    }


    /**
     * The single entry point for any invoice status change — replaces the scattered
     * if/elseif logic that previously allowed structurally illegal transitions
     * (e.g. a cancelled invoice being marked paid by an errant pay() call).
     *
     * @throws IllegalInvoiceStateTransitionException
     */
    public function transitionTo(InvoiceStatus $targetStatus): void
    {
        if (! $this->state()->canTransitionTo($targetStatus)) {
            throw new IllegalInvoiceStateTransitionException(
                "Cannot transition invoice #{$this->id} from [{$this->status->value}] to [{$targetStatus->value}]."
            );
        }

        $this->status = $targetStatus;
        $this->save();

        InvoiceStateFactory::make($targetStatus)->onEnter($this);
    }

    /**
     * Recomputes sub_total/total_amount/due_amount from the invoice's line items.
     * Called by InvoiceItemObserver whenever items change, and by recordPayment() below —
     * this Model owns the arithmetic, callers never compute totals manually.
     */
    public function recalculateTotals(): void
    {
        $subTotal = (float) $this->items()->get()->sum('total_price');

        $total = max(0.0, $subTotal + $this->tax_amount - $this->discount_amount);
        $due = max(0.0, $total - $this->paid_amount);

        $this->forceFill([
            'sub_total'        => $subTotal,
            'total_amount'     => $total,
            'due_amount'       => $due,
            'balance_amount'   => $due,
        ])->save();
    }

    /**
     * Records a payment against the invoice and drives the state machine — replaces the
     * old pay() method, which mutated `status` directly without transition validation.
     *
     * @throws IllegalInvoiceStateTransitionException
     */
    public function recordPayment(float $amount): void
    {
        $this->forceFill(['paid_amount' => $this->paid_amount + $amount])->save();
        $this->recalculateTotals();
        $this->refresh();

        $targetStatus = $this->due_amount <= 0.0 ? InvoiceStatus::Paid : InvoiceStatus::PartiallyPaid;

        if ($this->status !== $targetStatus) {
            $this->transitionTo($targetStatus);
        }
    }
}
