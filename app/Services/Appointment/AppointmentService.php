<?php

declare(strict_types=1);

namespace App\Services\Appointment;

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
                throw new \DomainException(__('The selected doctor is unavailable at this specific time slot.'));
            }

            $appointment = $this->stroeAppointemnt($data);

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
                    throw new \DomainException(__('The time slot is already booked for this doctor.'));
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
        return $appointment->update(['status' => 'cancelled']);
    }

    protected function isOverlapping(array $data): bool
    {
        $startTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $endTime = Carbon::parse($data['end_time'])->format('H:i:s');

        $truthValue = Appointment::where('doctor_id', $data['doctor_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('status', '!=', 'cancelled')
            ->where(static function ($query) use ($startTime, $endTime): void {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        return $truthValue;
    }

    protected function stroeAppointemnt(array $data): Appointment
    {
        $date = Carbon::parse($data['appointment_date'])->format('Y-m-d');
        $startTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $endTime = Carbon::parse($data['end_time'])->format('H:i:s');

        $appointment = Appointment::create([
            'patient_id'       => $data['patient_id'],
            'doctor_id'        => $data['doctor_id'],
            'appointment_date' => $date,
            'start_time'       => $startTime,
            'end_time'         => $endTime ?? null,
            'reason_for_visit' => $data['reason_for_visit'] ?? null,
            'doctor_notes'     => $data['doctor_notes'] ?? null,
            'status'           => 'scheduled',
        ]);
        return $appointment;
    }

    protected function getMessage(string $name = null, string $message, string $status)
    {
        return "";
    }
}
