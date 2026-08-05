<?php

declare(strict_types=1);

namespace App\Services\DentalRecord;

use App\Models\Appointment;
use App\Models\DentalRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use function App\Helpers\storage_engine;

/**
 * Class DentalRecordService
 * Handles clinical dental record mutations fully compliant with FileStorageServiceInterface.
 */
class DentalRecordService
{
    /**
     * Create a clinical dental record tied to a specific appointment and safely store any
     * X-Ray scan via the unified FileStorageServiceInterface.
     *
     * FIX (Coherence Audit): previously accepted a raw $data['patient_id']/$data['appointment_id']
     * pair trusted from client input, while Doctor\DentalRecordController actually already had
     * the route-bound Appointment model in scope and re-implemented this entire method inline
     * — including a side effect (marking the appointment 'completed') that did not exist here at
     * all. The signature now takes the Appointment directly: patient_id/doctor_id/appointment_id
     * are derived from it (removing a client-trust surface), and the appointment-completion rule
     * is centralized here as part of the Service's business logic, per the confirmed
     * Action-vs-Service distinction (Service = business logic with constraints).
     *
     * @param array<string, mixed> $data
     * @param Appointment $appointment
     * @param UploadedFile|null $xrayFile
     * @return DentalRecord
     */
    public function createRecord(array $data, Appointment $appointment, ?UploadedFile $xrayFile = null): DentalRecord
    {
        return DB::transaction(function () use ($data, $appointment, $xrayFile) {
            $xrayPath = null;

            if ($xrayFile) {
                $assetName = sprintf('xray_patient_%d_appointment_%d', $appointment->patient_id, $appointment->id);

                $xrayPath = storage_engine()->upload(
                    name: $assetName,
                    file: $xrayFile,
                    directory: 'doctor/xrays',
                    disk: 'public'
                );
            }

            $record = DentalRecord::create([
                'doctor_id'       => $appointment->doctor_id,
                'patient_id'      => $appointment->patient_id,
                'appointment_id'  => $appointment->id,
                'tooth_number'    => $data['tooth_number'] ?? null,
                'condition_type'  => $data['condition_type'],
                'description'     => $data['description'] ?? null,
                'xray_image_path' => $xrayPath,
            ]);

            // Business rule: authoring a dental record for an appointment marks it completed.
            $appointment->update([
                'status'       => 'completed',
                'doctor_notes' => $data['description'] ?? $appointment->doctor_notes,
            ]);

            return $record;
        });
    }

    /**
     * Safely update a clinical record using atomic replacement provided by storage_engine()->update().
     *
     * @param DentalRecord $dentalRecord
     * @param array<string, mixed> $data
     * @param UploadedFile|null $xrayFile
     * @return DentalRecord
     */
    public function updateRecord(DentalRecord $dentalRecord, array $data, ?UploadedFile $xrayFile = null): DentalRecord
    {
        return DB::transaction(function () use ($dentalRecord, $data, $xrayFile) {
            if ($xrayFile) {
                $assetName = sprintf('xray_patient_%d_tooth_%s', $dentalRecord->patient_id, $data['tooth_number'] ?? 'na');

                $data['xray_image_path'] = storage_engine()->update(
                    name: $assetName,
                    newFile: $xrayFile,
                    oldPath: $dentalRecord->xray_image_path,
                    directory: 'doctor/xrays',
                    disk: 'public'
                );
            }

            $dentalRecord->update($data);

            return $dentalRecord->refresh();
        });
    }

    /**
     * Delete dental record and purge stored X-Ray asset safely via storage_engine()->delete().
     *
     * @param DentalRecord $dentalRecord
     * @return bool|null
     */
    public function deleteRecord(DentalRecord $dentalRecord): ?bool
    {
        return DB::transaction(function () use ($dentalRecord) {
            if ($dentalRecord->xray_image_path) {
                storage_engine()->delete($dentalRecord->xray_image_path, 'public');
            }

            return $dentalRecord->delete();
        });
    }
}
