<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'treatment_course_id',
        'appointment_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'reason_for_visit',
        'doctor_notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'duration_minutes' => 'integer',
        'status'           => AppointmentStatus::class,
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

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function dentalRecord(): HasOne
    {
        return
            $this->hasOne(DentalRecord::class);
    }

    public function treatmentCourse(): BelongsTo
    {
        return
            $this->belongsTo(TreatmentCourse::class);
    }
}
