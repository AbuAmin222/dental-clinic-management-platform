<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\TreatmentCourseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreatmentCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'title',
        'tooth_number',
        'planned_sessions_count',
    ];

    protected $casts = [
        'tooth_number'             => 'integer',
        'planned_sessions_count'    => 'integer',
        'completed_sessions_count'  => 'integer',
        'status'                    => TreatmentCourseStatus::class,
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Re-derives the completed-sessions counter and status from the source of truth
     * (linked appointments) rather than incrementing/decrementing, which would drift
     * under cancellations or concurrent updates. Mirrors Invoice::recalculateTotals().
     */
    public function recalculateSessionsCount(): void
    {
        $completed = $this->appointments()->where('status', AppointmentStatus::Completed)->count();

        $this->update([
            'completed_sessions_count' => $completed,
            'status' => $completed >= ($this->planned_sessions_count ?? PHP_INT_MAX)
                ? TreatmentCourseStatus::Completed
                : $this->status,
        ]);
    }
}
