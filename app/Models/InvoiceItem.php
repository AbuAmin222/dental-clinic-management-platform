<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `total_price` is always computed server-side (see booted()) — never accept it directly
 * from a Request. Parent Invoice totals are kept in sync by InvoiceItemObserver, not by
 * this Model directly, to keep the two concerns decoupled (Observer pattern).
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'pricing_id',
        'item_name',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price'  => MoneyCast::class,
        'total_price' => MoneyCast::class,
        'quantity'    => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->total_price = $item->quantity * $item->unit_price;
        });
    }
}
