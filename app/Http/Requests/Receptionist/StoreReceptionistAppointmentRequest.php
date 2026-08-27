<?php

declare(strict_types=1);

namespace App\Http\Requests\Receptionist;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceptionistAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Receptionist->value) ?? false;
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

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patient_id.required'       => __('Please select a patient for this appointment.'),
            'patient_id.exists'         => __('The selected patient does not exist.'),
            'doctor_id.required'        => __('Please select a doctor for this appointment.'),
            'doctor_id.exists'          => __('The selected doctor does not exist.'),
            'appointment_date.required' => __('The appointment date is required.'),
            'appointment_date.date'     => __('The appointment date must be a valid date.'),
            'appointment_date.after'    => __('The appointment date must be after today.'),
            'start_time.required'       => __('The appointment start time is required.'),
            'reason_for_visit.max'      => __('The reason for visit must not exceed 500 characters.'),
        ];
    }
}
