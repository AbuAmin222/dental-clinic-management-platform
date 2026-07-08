<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'reason_for_visit',
        'doctor_notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
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

    public function invoices(): HasMany
    {
        return
            $this->hasMany(Invoice::class);
    }

    public function dentalRecord(): HasOne
    {
        return
            $this->hasOne(DentalRecord::class);
    }
}
