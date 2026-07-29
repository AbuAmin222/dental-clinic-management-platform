<?php

declare(strict_types=1);

namespace App\Http\Requests\Doctor;

use App\Models\DentalRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDentalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('create', DentalRecord::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'patient_id'     => ['required', 'integer', Rule::exists('patients', 'id')],
            'appointment_id' => ['required', 'integer', Rule::exists('appointments', 'id')],
            'tooth_number'   => ['required', 'integer', 'between:1,32'],
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
            'patient_id.required'     => __('The patient reference is required.'),
            'patient_id.exists'       => __('The selected patient does not exist.'),
            'appointment_id.required' => __('The appointment reference is required.'),
            'appointment_id.exists'   => __('The selected appointment does not exist.'),
            'tooth_number.required'   => __('Tooth number must be specified.'),
            'tooth_number.between'    => __('Tooth number must be between 1 and 32.'),
            'condition_type.required' => __('Clinical condition type is required.'),
            'xray_image.image'        => __('The uploaded file must be a valid image.'),
            'xray_image.mimes'        => __('The X-Ray image format must be jpeg, png, jpg, or webp.'),
            'xray_image.max'          => __('The X-Ray image size must not exceed 5MB.'),
        ];
    }
}
