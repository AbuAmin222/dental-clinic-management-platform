<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor;

use App\Contracts\Storage\FileStorageServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DentalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class DentalRecordController
 *
 * Governs the lifecycle of highly classified dental medical health records, handling secure ingestion pipelines.
 * Utilizes FileStorageServiceInterface abstraction layer to promote loose coupling and testability.
 *
 * @package App\Http\Controllers\Doctor
 */
class DentalRecordController extends Controller
{
    /**
     * Inject file storage abstraction service via constructor injection.
     *
     * @param FileStorageServiceInterface $storageService
     */
    public function __construct(
        protected readonly FileStorageServiceInterface $storageService
    ) {}

    /**
     * Display the clinical record creation interface hydrated with critical contextual relation entities.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Inertia\Response
     */
    public function create(Appointment $appointment): InertiaResponse
    {
        // Strictly authorize access using the AppointmentPolicy and DentalRecordPolicy
        $this->authorize('update', $appointment);
        $this->authorize('create', DentalRecord::class);

        $appointment->load(['patient.user', 'doctor.user']);

        return Inertia::render('Doctor/DentalRecords/Create', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * Store an isolated diagnostic dental record and securely process clinical telemetry uploads within an atomic transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        // Enforce strong dynamic authorization barriers before execution
        $this->authorize('update', $appointment);
        $this->authorize('create', DentalRecord::class);

        $appointment->loadMissing('patient.user');

        $input = $request->all();

        Validator::make($input, [
            'tooth_number'   => ['nullable', 'string', 'max:2'],
            'condition_type' => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string', 'min:5'],
            'xray_image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:4096'],
        ], [
            'tooth_number.max'        => 'Tooth number must be less than 2 characters.',
            'condition_type.required' => 'Condition Type is required.',
            'description.required'    => 'Description is required.',
            'description.min'         => 'Description must be greater than 5 characters.',
            'xray_image.max'          => 'X-Ray photo size must be less than 4MB.',
            'xray_image.image'        => 'The uploaded file must be a valid image.',
        ])->validate();

        // Enforce ACID relational data persistence boundaries
        DB::transaction(function () use ($input, $appointment, $request): void {
            $path = null;

            if ($request->hasFile('xray_image') && $input['xray_image'] instanceof UploadedFile) {
                $patientUser = $appointment->patient?->user;
                $patientName = $patientUser ? sprintf('%s %s', $patientUser->first_name, $patientUser->last_name) : 'Anonymous';
                $path = $this->handleFileUpload($patientName, $input['xray_image'], 'xrays');
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
                'doctor_notes' => $input['description'],
            ]);
        });

        return redirect()
            ->route('doctor.dashboard')
            ->with('success', 'Dental medical record added and appointment marked as completed.');
    }

    /**
     * Dispatch medical binary file streams directly through the injected abstraction storage service.
     *
     * @param  string  $name
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder
     * @return string
     */
    protected function handleFileUpload(string $name, UploadedFile $file, string $folder): string
    {
        return $this->storageService->upload($name, $file, $folder, 'local');
    }
}
