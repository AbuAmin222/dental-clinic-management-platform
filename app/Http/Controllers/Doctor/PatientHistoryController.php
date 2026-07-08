<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Inertia\Inertia;
use Inertia\Response;

class PatientHistoryController extends Controller
{
    /**
     * Show midecal achive for doctor
     */
    public function show(Patient $patient): Response
    {
        $patient->load(['user']);

        $history = $patient->dentalRecords()
            ->with(['doctor.user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'tooth_number' => $record->tooth_number ?? 'General / Non-specific',
                    'condition_type' => $record->condition_type,
                    'description' => $record->description,
                    'has_xray' => (bool) $record->xray_image_path,
                    'date' => $record->created_at->format('M d, Y - h:i A'),
                    'doctor_name' => 'Dr. ' . $record->doctor->user->first_name . ' ' . $record->doctor->user->last_name,
                ];
            });

        return Inertia::render('Doctor/Patients/History', [
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->user->first_name . ' ' . $patient->user->last_name,
                'identity_number' => $patient->user->identity_number,
                'phone' => $patient->user->phone,
                'gender' => $patient->user->gender,
                'age' => $patient->user->date_of_birth ? \Carbon\Carbon::parse($patient->user->date_of_birth)->age : 'N/A',
                'blood_group' => $patient->blood_group ?? 'Not Specified',
                'allergies' => $patient->allergies,
                'chronic_diseases' => $patient->chronic_diseases,
                'emergency_contact' => $patient->emergency_contact_name . ' (' . $patient->emergency_contact_phone . ')',
            ],
            'history' => $history,
        ]);
    }
}
