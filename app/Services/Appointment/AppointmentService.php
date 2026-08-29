<?php

declare(strict_types=1);

namespace App\Services\Appointment;

use App\Enums\AppointmentStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class AppointmentService
 * Encapsulates transactional business logic for scheduling and managing clinical appointments.
 */
class AppointmentService
{
    /**
     * Default appointment duration applied when the caller does not supply an explicit
     * end_time (e.g. the patient self-booking flow, which only collects start_time).
     */
    private function defaultDurationMinutes(): int
    {
        return (int) config('clinic.appointments.default_duration_minutes', 30);
    }

    /**
     * Book a new clinical appointment preventing schedule conflicts.
     *
     * @param array<string, mixed> $data
     * @param int $patientId
     * @return Appointment
     */
    public function bookAppointment(array $data, int $patientId): Appointment
    {
        return DB::transaction(function () use ($data, $patientId) {
            Doctor::where('id', $data['doctor_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isOverlapping($data)) {
                throw new BusinessRuleViolationException(
                    __('The selected doctor is unavailable at this specific time slot.')
                );
            }

            $appointment = $this->storeAppointment($data);

            return $appointment;
        });
    }

    /**
     * Update existing appointment schedule or details safely.
     *
     * @param Appointment $appointment
     * @param array<string, mixed> $data
     * @return Appointment
     */
    public function updateAppointment(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data) {
            if (isset($data['start_time'], $data['appointment_date'])) {
                if ($this->isOverlapping($data)) {
                    throw new BusinessRuleViolationException(
                        __('The time slot is already booked for this doctor.')
                    );
                }
            }

            $appointment->update($data);

            return $appointment->refresh();
        });
    }

    /**
     * Cancel an active appointment.
     *
     * @param Appointment $appointment
     * @return bool
     */
    public function cancelAppointment(Appointment $appointment): bool
    {
        return $appointment->update(['status' => AppointmentStatus::Cancelled]);
    }

    protected function isOverlapping(array $data): bool
    {
        $startTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $endTime = $this->resolveEndTime($data);

        $truthValue = Appointment::where('doctor_id', $data['doctor_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('status', '!=', AppointmentStatus::Cancelled)
            ->where(static function ($query) use ($startTime, $endTime): void {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        return $truthValue;
    }

    protected function storeAppointment(array $data): Appointment
    {
        $date = Carbon::parse($data['appointment_date'])->format('Y-m-d');
        $startTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $endTime = $this->resolveEndTime($data);

        $appointment = Appointment::create([
            'patient_id'           => $data['patient_id'],
            'doctor_id'            => $data['doctor_id'],
            'treatment_course_id'  => $data['treatment_course_id'] ?? null,
            'appointment_date'     => $date,
            'start_time'           => $startTime,
            'end_time'             => $endTime,
            'duration_minutes'     => Carbon::parse($startTime)->diffInMinutes(Carbon::parse($endTime)),
            'reason_for_visit'     => $data['reason_for_visit'] ?? null,
            'doctor_notes'         => $data['doctor_notes'] ?? null,
            'status'               => $data['status'] ?? AppointmentStatus::Confirmed
        ]);
        return $appointment;
    }

    /**
     * Resolve the appointment end_time, defaulting to start_time plus a fixed
     * provisional duration when the caller (e.g. patient self-booking) does not supply one.
     * Centralizing this here means both isOverlapping() and storeAppointment() always see
     * a consistent, non-null end_time and neither can throw on a missing array key.
     *
     * @param array<string, mixed> $data
     * @return string H:i:s formatted end time.
     */
    protected function resolveEndTime(array $data): string
    {
        if (!empty($data['end_time'])) {
            return Carbon::parse($data['end_time'])->format('H:i:s');
        }

        return Carbon::parse($data['start_time'])
            ->addMinutes($this->defaultDurationMinutes())
            ->format('H:i:s');
    }
}
