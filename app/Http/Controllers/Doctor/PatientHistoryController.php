<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\DentalRecord;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class PatientHistoryController
 *
 * Orchestrates clinical diagnostics history visualizer modules allowing Doctors to survey patient archives under high privacy controls.
 *
 * @package App\Http\Controllers\Doctor
 */
class PatientHistoryController extends Controller
{
    /**
     * Render a structured patient health record file encompassing clinical history and historical treatment sheets.
     *
     * FIX (2026-08-04): previously only checked `viewAny(DentalRecord::class)` -- a role-level
     * gate that let ANY doctor view the full clinical history of ANY patient, even one they had
     * never treated. Now additionally enforces `view($patient)` via the newly-created
     * PatientPolicy, whose DoctorAuthorizationStrategy requires an existing Appointment between
     * this doctor and this patient before granting access.
     *
     * @param  \App\Models\Patient  $patient
     * @return \Inertia\Response
     */
    public function show(Patient $patient): InertiaResponse
    {
        $this->authorize('viewAny', DentalRecord::class);
        $this->authorize('view', $patient);

        $patient->load(['user']);

        $history = $patient->dentalRecords()
            ->with(['doctor.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(static function (DentalRecord $record): array {
                return [
                    'id'             => $record->id,
                    'tooth_number'   => $record->tooth_number ?? 'General / Non-specific',
                    'condition_type' => $record->condition_type,
                    'description'    => $record->description,
                    'has_xray'       => (bool) $record->xray_image_path,
                    'date'           => $record->created_at ? $record->created_at->format('M d, Y - h:i A') : 'N/A',
                    'doctor_name'    => $record->doctor?->user
                        ? sprintf('Dr. %s %s', $record->doctor->user->first_name, $record->doctor->user->last_name)
                        : 'Unknown Doctor',
                ];
            });

        $userProfile = $patient->user;

        return Inertia::render('Doctor/Patients/History', [
            'patient' => [
                'id'                => $patient->id,
                'name'              => $userProfile ? sprintf('%s %s', $userProfile->first_name, $userProfile->last_name) : 'N/A',
                'identity_number'   => $userProfile?->identity_number,
                'phone'             => $userProfile?->phone,
                'gender'            => $userProfile?->gender,
                'age'               => $userProfile?->date_of_birth ? Carbon::parse($userProfile->date_of_birth)->age : 'N/A',
                'blood_group'       => $patient->blood_group ?? 'Not Specified',
                'allergies'         => $patient->allergies,
                'chronic_diseases'  => $patient->chronic_diseases,
                'emergency_contact' => sprintf('%s (%s)', $patient->emergency_contact_name, $patient->emergency_contact_phone),
            ],
            'history' => $history,
        ]);
    }
}
