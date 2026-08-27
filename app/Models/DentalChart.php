<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'tooth_number',
        'condition',
        'notes',
    ];

    protected $casts = [
        'tooth_number' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * The single sanctioned write path for this Model — overwrites the existing row for
     * this (patient, tooth) pair if one exists, or creates it otherwise. Centralizing this
     * here means no Controller/Service ever needs to know the underlying constraint exists.
     */
    public static function upsertForTooth(int $patientId, int $doctorId, int $toothNumber, string $condition, ?string $notes = null): self
    {
        return self::updateOrCreate(
            ['patient_id' => $patientId, 'tooth_number' => $toothNumber],
            ['doctor_id' => $doctorId, 'condition' => $condition, 'notes' => $notes],
        );
    }
}
