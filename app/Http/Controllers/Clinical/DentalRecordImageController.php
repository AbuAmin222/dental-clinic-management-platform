<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinical;

use App\Contracts\Storage\FileStorageServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\DentalRecord;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DentalRecordImageController
 *
 * Highly Secure Clinical Telemetry Streamer.
 * Leverages Abstraction Layer (FileStorageServiceInterface) to stream X-Ray binary records,
 * enforcing low coupling and multi-tenant authorization boundaries.
 *
 * @package App\Http\Controllers\Clinical
 */
class DentalRecordImageController extends Controller
{
    /**
     * Inject file storage abstraction service via constructor.
     */
    public function __construct(
        protected readonly FileStorageServiceInterface $storageService
    ) {}

    /**
     * Stream a dental record's X-ray scan securely with high disk-I/O efficiency.
     *
     * DentalRecordPolicy::view() requires (User, DentalRecord, Appointment). This is now safe
     * unconditionally: `dental_records.appointment_id` was made required at the schema level
     * (business decision confirmed 2026-08-11 — a dental record can never legitimately exist
     * without a linked appointment), so `$dentalRecord->appointment` is guaranteed non-null.
     * The previous 404 fallback for a null appointment was removed along with the decision —
     * it can no longer occur, and keeping dead defensive code around a constraint the database
     * itself now enforces would only obscure that guarantee for the next reader.
     *
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(DentalRecord $dentalRecord): Response
    {
        $this->authorize('view', [$dentalRecord, $dentalRecord->appointment]);

        $path = $dentalRecord->xray_image_path;

        abort_if(
            empty($path) || !$this->storageService->exists((string) $path, 'local'),
            404,
            'No X-ray file located for this record.'
        );

        return $this->storageService->response((string) $path, 'local');
    }
}
