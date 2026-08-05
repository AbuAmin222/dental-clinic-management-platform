<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreDentalRecordRequest;
use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Services\DentalRecord\DentalRecordService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class DentalRecordController
 *
 * Governs the lifecycle of highly classified dental medical health records, handling secure
 * ingestion pipelines.
 *
 * FIX (Coherence Audit): this Controller previously bypassed both StoreDentalRecordRequest and
 * DentalRecordService entirely, re-implementing file upload (via a directly injected
 * FileStorageServiceInterface) and record creation inline with its own separate validation
 * rules. It now delegates fully to the Service, which centralizes the "creating a dental record
 * marks the appointment completed" business rule that previously lived only here.
 *
 * @package App\Http\Controllers\Doctor
 */
class DentalRecordController extends Controller
{
    public function __construct(
        protected readonly DentalRecordService $dentalRecordService
    ) {}

    /**
     * Display the clinical record creation interface hydrated with critical contextual relation entities.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return \Inertia\Response
     */
    public function create(Appointment $appointment): InertiaResponse
    {
        $this->authorize('update', $appointment);
        $this->authorize('create', DentalRecord::class);

        $appointment->load(['patient.user', 'doctor.user']);

        return Inertia::render('Doctor/DentalRecords/Create', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * Store an isolated diagnostic dental record and securely process clinical telemetry uploads.
     *
     * @param  \App\Http\Requests\Doctor\StoreDentalRecordRequest  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreDentalRecordRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);
        $this->authorize('create', DentalRecord::class);

        $this->dentalRecordService->createRecord(
            $request->validated(),
            $appointment,
            $request->file('xray_image')
        );

        return redirect()
            ->route('doctor.dashboard')
            ->with('success', 'Dental medical record added and appointment marked as completed.');
    }
}
