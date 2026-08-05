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
     * DentalRecordPolicy::view() requires (User, DentalRecord, Appointment) — the
     * previous call `$this->authorize('view', $dentalRecord)` supplied only the DentalRecord,
     * which would throw a TypeError as soon as Laravel tried to invoke the Policy method.
     * The related Appointment is now resolved and passed explicitly. Since
     * dental_records.appointment_id is nullable at the schema level while the Policy strategies
     * require a non-null Appointment, a record with no linked appointment now fails closed
     * (404) instead of crashing — flagged in PENDING_TASKS.md pending a business decision on
     * whether a dental record may legitimately exist without an appointment.
     *
     * @param  \App\Models\DentalRecord  $dentalRecord
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(DentalRecord $dentalRecord): Response
    {
        $appointment = $dentalRecord->appointment;

        abort_if($appointment === null, 404, 'This dental record has no linked appointment context.');

        $this->authorize('view', [$dentalRecord, $appointment]);

        $path = $dentalRecord->xray_image_path;

        abort_if(
            empty($path) || !$this->storageService->exists((string) $path, 'local'),
            404,
            'No X-ray file located for this record.'
        );

        return $this->storageService->response((string) $path, 'local');
    }
}
