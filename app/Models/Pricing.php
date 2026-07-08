<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'service_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the doctor who provides this priced service.
     */
    public function doctor(): BelongsTo
    {
        return
            $this->belongsTo(Doctor::class);
    }
}
