<?php

declare(strict_types=1);

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreAppointmentRequest
 * Handles the payload routing constraints required to initialize a verified appointment timeline block.
 */
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'patient';
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
}
