<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Pricing
 * * Manages clinical billing definitions and dynamic doctor-scoped service pricing rates.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $doctor_id
 * @property string $service_name
 * @property string $amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read \App\Models\Doctor $doctor
 */
class Pricing extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pricings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id',
        'service_name',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => MoneyCast::class,
    ];

    /**
     * Retrieve the dedicated professional Doctor structural entity provider for this pricing schema.
     *
     * @return BelongsTo<Doctor, self>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
