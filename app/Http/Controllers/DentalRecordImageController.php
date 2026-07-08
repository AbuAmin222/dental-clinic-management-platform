<?php

namespace App\Http\Controllers;

use App\Models\DentalRecord;
use Illuminate\Support\Facades\Storage;

class DentalRecordImageController extends Controller
{
    /**
     * Stream a dental record's X-ray scan — only to a user the
     * DentalRecordPolicy::view() allows (the treating doctor or
     * the owning patient). Nobody else, even with the direct link.
     */
    public function show(DentalRecord $dentalRecord)
    {
        $this->authorize('view', $dentalRecord);

        abort_if(!$dentalRecord->xray_image_path, 404, 'No X-ray on file for this record.');

        return Storage::disk('local')->response($dentalRecord->xray_image_path);
    }
}
