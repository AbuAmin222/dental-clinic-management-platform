<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialization_id',
        'license_number',
        'bio',
        'experience_years',
    ];

    protected $casts = [
        'experience_years' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return
            $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return
            $this->belongsTo(Specialization::class);
    }

    public function appointments(): HasMany
    {
        return
            $this->hasMany(Appointment::class);
    }

    public function dentalRecords(): HasMany
    {
        return
            $this->hasMany(DentalRecord::class);
    }

    public function pricings(): HasMany
    {
        return
            $this->hasMany(Pricing::class);
    }

    public function invoices(): HasMany
    {
        return
            $this->hasMany(Invoice::class);
    }
}
