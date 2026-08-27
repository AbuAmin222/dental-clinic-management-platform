<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Immutable append-only audit ledger. Never updated, never deleted — enforced structurally
 * in booted(), not just by convention.
 */
class FinancialAuditLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'financial_id',
        'invoice_id',
        'action',
        'amount_changed',
        'payload_before',
        'payload_after',
        'ip_address',
    ];

    protected $casts = [
        'amount_changed'  => MoneyCast::class,
        'payload_before'  => 'array',
        'payload_after'   => 'array',
    ];

    public function financial(): BelongsTo
    {
        return $this->belongsTo(Financial::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Financial audit logs are immutable and cannot be updated.');
        });

        static::deleting(function (): never {
            throw new LogicException('Financial audit logs are immutable and cannot be deleted.');
        });
    }
}
