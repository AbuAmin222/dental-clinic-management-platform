<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dental_records';

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_id',
        'tooth_number',
        'condition_type',
        'description',
        'xray_image_path',
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

    public function appointment(): BelongsTo
    {
        return
            $this->belongsTo(Appointment::class);
    }

    /**
     * Auto append virtual absolute URL for the X-Ray scan image.
     */
    protected function xrayUrl(): Attribute
    {
        return
            Attribute::make(
                get: fn() => storage_engine()->url($this->xray_image_path, 'public', 'https://placehold.co/600x400?text=No+Scan')
            );
    }

    /**
     * File handling safety on model lifecycle.
     */
    protected static function booted()
    {
        static::deleting(function ($record) {
            if ($record->xray_image_path) {
                storage_engine()->delete($record->xray_image_path);
            }
        });
    }
}
