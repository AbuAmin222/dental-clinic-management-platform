<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use function App\Helpers\storage_engine;

/**
 * Class DentalRecord
 * * Represents an individual clinical dental record entry inside the system.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $doctor_id
 * @property int $patient_id
 * @property int $appointment_id
 * @property int $tooth_number
 * @property string $condition_type
 * @property string|null $description
 * @property string|null $xray_image_path
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Doctor $doctor
 * @property-read \App\Models\Patient $patient
 * @property-read \App\Models\Appointment $appointment
 * @property-read string $xray_url
 */
class DentalRecord extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dental_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_id',
        'tooth_number',
        'condition_type',
        'description',
        'xray_image_path',
    ];


    /**
     * Define structural inverse relationship to the professional Doctor instance.
     *
     * @return BelongsTo<Doctor, self>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Define structural inverse relationship to the registered Patient.
     *
     * @return BelongsTo<Patient, self>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Define structural relationship mapping to the specific physical clinical Appointment.
     *
     * @return BelongsTo<Appointment, self>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Dynamic evaluation engine resolving virtual absolute URLs for physical X-Ray scan assets.
     * Highly typed wrapper conforming to modern Laravel Attribute specifications.
     *
     * @return Attribute<string, never>
     */
    protected function xrayUrl(): Attribute
    {
        return Attribute::make(
            get: fn(): string => storage_engine()->url(
                $this->xray_image_path,
                'public',
                'https://placehold.co/600x400?text=No+Scan'
            )
        );
    }

    /**
     * Handle physical file disposal carefully during model lifecycle operations.
     * Hooked onto 'forceDeleted' to ensure soft-delete cycles never isolate or corrupt data integrity.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::forceDeleted(static function (self $record): void {
            if ($record->xray_image_path) {
                storage_engine()->delete($record->xray_image_path);
            }
        });
    }
}
