<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DentalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;

class DentalRecordController extends Controller
{
    /**
     * Show the form for creating a new dental record.
     */
    public function create(Appointment $appointment)
    {
        $appointment->load(['patient.user', 'doctor.user']);

        return inertia('Doctor/DentalRecords/Create', [
            'appointment' => $appointment
        ]);
    }

    /**
     * Store a newly created dental record in storage.
     */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        // 1. Ensure relations are present to avoid null pointer exceptions when building file path
        $appointment->loadMissing('patient.user');

        $input = $request->all();

        Validator::make($input, [
            'tooth_number'   => 'nullable|string|max:2',
            'condition_type' => 'required|string|max:255',
            'description'    => 'required|string|min:5',
            'xray_image'     => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ], [
            'tooth_number.max'         => 'Tooth number must be less than 2 characters.',
            'condition_type.required'  => 'Condition Type is required.',
            'description.required'     => 'Description is required.',
            'description.min'          => 'Description must be greater than 5 characters.',
            'xray_image.max'           => 'X-Ray photo size must be less than 4MB.',
            'xray_image.image'         => 'The uploaded file must be a valid image.',
        ])->validate();

        DB::transaction(function () use ($input, $appointment, $request) {

            $patientName = $appointment->patient->user->first_name . ' ' . $appointment->patient->user->last_name;
            $folder = 'xrays';
            $path = null;

            if ($request->hasFile('xray_image')) {
                $path = $this->handleFileUpload($patientName, $input['xray_image'], $folder);
            }

            DentalRecord::create([
                'doctor_id'       => $appointment->doctor_id,
                'patient_id'      => $appointment->patient_id,
                'appointment_id'  => $appointment->id,
                'tooth_number'    => $input['tooth_number'] ?? null,
                'condition_type'  => $input['condition_type'],
                'description'     => $input['description'],
                'xray_image_path' => $path,
            ]);

            $appointment->update([
                'status'       => 'completed',
                'doctor_notes' => $input['description']
            ]);
        });

        return redirect()->route('doctor.dashboard')
            ->with('success', 'Dental medical record added and appointment marked as completed.');
    }

    /**
     * Centralized execution for physical file system storage.
     *
     * X-rays are medical images — stored on the PRIVATE 'local' disk, not
     * 'public'. They're served back out only through DentalRecordImageController,
     * which checks DentalRecordPolicy first. Never link to this path directly.
     */
    protected function handleFileUpload(string $name, UploadedFile $file, string $folder): string
    {
        return storage_engine()->upload($name, $file, $folder, 'local');
    }
}
