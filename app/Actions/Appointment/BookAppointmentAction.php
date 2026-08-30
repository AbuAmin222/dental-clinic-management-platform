<?php

declare(strict_types=1);

namespace App\Actions\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookAppointmentAction
{
    /**
     * Atomically execute appointment reservation with pessimistic locking and dynamic status handling.
     *
     * @param array<string, mixed> $data
     * @return Appointment
     * @throws ValidationException
     */
    public function execute(array $data): Appointment
    {
        $date = Carbon::parse($data['appointment_date'])->format('Y-m-d');
        $startTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $endTime = Carbon::parse($data['end_time'])->format('H:i:s');
        $status = $data['status'] ?? AppointmentStatus::Pending->value;

        return DB::transaction(function () use ($data, $date, $startTime, $endTime, $status): Appointment {
            Doctor::where('id', $data['doctor_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $isOverlapping = Appointment::where('doctor_id', $data['doctor_id'])
                ->where('appointment_date', $date)
                ->where('status', '!=', AppointmentStatus::Cancelled)
                ->where(static function ($query) use ($startTime, $endTime): void {
                    $query->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->exists();

            if ($isOverlapping) {
                throw ValidationException::withMessages([
                    'appointment_date' => 'The selected time slot conflicts with an existing appointment for this doctor.'
                ]);
            }

            return Appointment::create([
                'patient_id'       => $data['patient_id'],
                'doctor_id'        => $data['doctor_id'],
                'appointment_date' => $date,
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'status'           => $status,
                'reason_for_visit' => $data['reason_for_visit'] ?? null,
            ]);
        });
    }
}
