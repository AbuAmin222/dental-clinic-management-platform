<?php

declare(strict_types=1);

namespace App\Http\Requests\Receptionist;

use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReceptionistAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Receptionist->value) ?? false;
    }

    /**
     * The Receptionist form (resources/js/Pages/Receptionist/Appointments/Create.vue)
     * sends ONE combined datetime-local value in `appointment_date`
     * (e.g. "2026-08-29T14:30"). Split it here into date + start_time + end_time
     * BEFORE validation runs, so the required `start_time`/`end_time` rules below
     * (and BookAppointmentAction, which needs both) always receive real values.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('appointment_date')) {
            return;
        }

        $parsed = Carbon::parse($this->input('appointment_date'));
        $durationMinutes = (int) config('clinic.appointments.default_duration_minutes', 30);

        $this->merge([
            'appointment_date' => $parsed->format('Y-m-d'),
            'start_time'       => $this->filled('start_time')
                ? $this->input('start_time')
                : $parsed->format('H:i:s'),
            'end_time'         => $this->filled('end_time')
                ? $this->input('end_time')
                : $parsed->copy()->addMinutes($durationMinutes)->format('H:i:s'),
        ]);
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s', 'after:start_time'],
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
            'appointment_date.after_or_equal' => __('The appointment date cannot be in the past.'),
            'start_time.required'       => __('The appointment start time is required.'),
            'start_time.date_format'    => __('The appointment start time must be in HH:MM:SS format.'),
            'end_time.required'         => __('The appointment end time is required.'),
            'end_time.date_format'      => __('The appointment end time must be in HH:MM:SS format.'),
            'end_time.after'            => __('The appointment end time must be after the start time.'),
            'reason_for_visit.max'      => __('The reason for visit must not exceed 500 characters.'),
        ];
    }
}
