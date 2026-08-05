<?php

declare(strict_types=1);

namespace App\Http\Requests\Doctor;

use App\Models\DentalRecord;
use Illuminate\Foundation\Http\FormRequest;

class StoreDentalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Note: this only checks the role-level 'create' ability. Ownership of the specific
     * appointment (is this doctor actually assigned to it?) is separately enforced by
     * Doctor\DentalRecordController via $this->authorize('update', $appointment) — kept
     * as a distinct check because the Appointment is a route parameter, not part of this
     * request's own validated payload.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('create', DentalRecord::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * FIX (Coherence Audit): previously defined patient_id/appointment_id rules here that
     * were never actually used — DentalRecordController performed its own separate, looser,
     * inline Validator::make() with a different rule set entirely (e.g. tooth_number as a
     * 2-character string instead of an integer 1-32, description required with min:5 vs
     * nullable here). Both this class and the manual validator are now unified into this
     * single source of truth. patient_id/appointment_id are removed because the doctor
     * dental-record routes are always scoped to a route-bound Appointment
     * (appointments/{appointment}/dental-record/...) — trusting those IDs from client input
     * would be redundant at best and a spoofing surface at worst.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'tooth_number'   => ['nullable', 'integer', 'between:1,32'],
            'condition_type' => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string', 'min:5', 'max:2000'],
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
            'description.required'    => __('Description is required.'),
            'description.min'         => __('Description must be at least 5 characters.'),
            'xray_image.image'        => __('The uploaded file must be a valid image.'),
            'xray_image.mimes'        => __('The X-Ray image format must be jpeg, png, jpg, or webp.'),
            'xray_image.max'          => __('The X-Ray image size must not exceed 5MB.'),
        ];
    }
}
