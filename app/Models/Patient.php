<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blood_group',
        'allergies',
        'chronic_diseases',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_notes',
    ];

    public function user(): BelongsTo
    {
        return
            $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return
            $this->hasMany(Appointment::class);
    }

    public function invoices(): HasMany
    {
        return
            $this->hasMany(Invoice::class);
    }

    public function dentalRecords(): HasMany
    {
        return
            $this->hasMany(DentalRecord::class);
    }
}
