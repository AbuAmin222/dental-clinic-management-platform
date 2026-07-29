<?php

declare(strict_types=1);

namespace App\Services\DentalRecord;

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
     * Create a clinical dental record and safely store X-Ray scans via unified FileStorageServiceInterface.
     *
     * @param array<string, mixed> $data
     * @param int $doctorId
     * @param UploadedFile|null $xrayFile
     * @return DentalRecord
     */
    public function createRecord(array $data, int $doctorId, ?UploadedFile $xrayFile = null): DentalRecord
    {
        return DB::transaction(function () use ($data, $doctorId, $xrayFile) {
            $xrayPath = null;

            if ($xrayFile) {
                $assetName = sprintf('xray_patient_%d_tooth_%d', $data['patient_id'], $data['tooth_number']);

                $xrayPath = storage_engine()->upload(
                    name: $assetName,
                    file: $xrayFile,
                    directory: 'doctor/xrays',
                    disk: 'public'
                );
            }

            return DentalRecord::create([
                'doctor_id'       => $doctorId,
                'patient_id'      => $data['patient_id'],
                'appointment_id'  => $data['appointment_id'],
                'tooth_number'    => $data['tooth_number'],
                'condition_type'  => $data['condition_type'],
                'description'     => $data['description'] ?? null,
                'xray_image_path' => $xrayPath,
            ]);
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
                $assetName = sprintf('xray_patient_%d_tooth_%d', $dentalRecord->patient_id, $data['tooth_number']);

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
