<?php

declare(strict_types=1);

namespace App\Http\Requests\Doctor;

use App\Models\DentalRecord;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDentalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * FIX (Coherence Audit): DentalRecordPolicy::update() requires
     * (User, DentalRecord, Appointment) — the previous call `$user->can('update', $dentalRecord)`
     * supplied only the DentalRecord, which would throw a TypeError as soon as the Policy
     * method was invoked. The linked Appointment is now resolved via the DentalRecord's own
     * relationship and passed explicitly.
     *
     * Note: no route currently wires this FormRequest to any Controller (DentalRecordController
     * only implements create()/store() — there is no update route registered in
     * DoctorRouteRegistrar). This fix keeps the class correct and ready for when an update
     * flow is added, without inventing a route that does not exist yet.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $dentalRecord = $this->route('dental_record') ?? $this->route('dentalRecord');

        if ($user === null || ! ($dentalRecord instanceof DentalRecord)) {
            return false;
        }

        $appointment = $dentalRecord->appointment;

        if ($appointment === null) {
            return false;
        }

        return $user->can('update', [$dentalRecord, $appointment]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'tooth_number'   => ['nullable', 'integer', 'between:1,32'],
            'condition_type' => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'xray_image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tooth_number.integer'    => __('Tooth number must be a valid number.'),
            'tooth_number.between'    => __('Tooth number must be between 1 and 32.'),
            'condition_type.required' => __('Clinical condition type is required.'),
            'xray_image.image'        => __('The uploaded file must be a valid image.'),
            'xray_image.mimes'        => __('The X-Ray image format must be jpeg, png, jpg, or webp.'),
            'xray_image.max'          => __('The X-Ray image size must not exceed 5MB.'),
        ];
    }
}
