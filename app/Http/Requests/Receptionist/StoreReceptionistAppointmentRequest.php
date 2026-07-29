<?php

declare(strict_types=1);

namespace App\Http\Requests\Receptionist;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceptionistAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'receptionist';
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after:today'],
            'start_time' => ['required', 'string'],
            'reason_for_visit' => ['nullable', 'string', 'max:500'],
        ];
    }
}
