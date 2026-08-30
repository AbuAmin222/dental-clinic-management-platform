<?php

declare(strict_types=1);

namespace App\Http\Requests\Patient;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreAppointmentRequest
 * Handles the payload routing constraints required to initialize a verified appointment timeline block.
 */
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole(UserRole::Patient->value);
    }

    /**
     * Normalize incoming payload before validation logic runs.
     */
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if (!$this->has('status') && $patientId = $this->user()?->patient?->id && $this->status != AppointmentStatus::Pending->value) {
            $mergeData['status'] = AppointmentStatus::Pending->value;
        }

        // 1. Context Resolution: Auto-inject patient_id if missing (e.g., Patient Portal)
        if (!$this->has('patient_id') && $patientId = $this->user()?->patient?->id) {
            $mergeData['patient_id'] = $patientId;
        }

        // 2. Format Normalization: Ensure H:i:s format
        if ($this->has('start_time') && is_string($this->start_time)) {

            if (preg_match('/^\d{2}:\d{2}$/', $this->start_time)) {
                $mergeData['start_time'] = $this->start_time . ':00';
            }
        }
        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    public function rules(): array
    {
        return [
            'doctor_id'        => ['required', 'integer', 'exists:doctors,id'],
            'patient_id'       => ['required', 'integer', 'exists:patients,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time'       => ['required', 'date_format:H:i:s'],
            'reason_for_visit' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doctor_id.required'         => __('Please select a doctor for this appointment.'),
            'doctor_id.exists'           => __('The selected doctor does not exist.'),
            'patient_id.required'        => __('Patient information is required for this appointment.'),
            'patient_id.exists'          => __('The selected patient does not exist.'),
            'appointment_date.required'  => __('The appointment date is required.'),
            'appointment_date.date'      => __('The appointment date must be a valid date.'),
            'appointment_date.after_or_equal' => __('The appointment date cannot be in the past.'),
            'start_time.required'        => __('The appointment start time is required.'),
            'start_time.date_format'     => __('The appointment start time must be in HH:MM:SS format.'),
            'reason_for_visit.max'       => __('The reason for visit must not exceed 500 characters.'),
        ];
    }
}
