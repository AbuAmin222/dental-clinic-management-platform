<?php

declare(strict_types=1);

namespace App\Http\Requests\Doctor;

use App\Models\DentalRecord;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $toothMin = (int) config('clinic.dental_chart.tooth_number_min', 1);
        $toothMax = (int) config('clinic.dental_chart.tooth_number_max', 32);
        $xrayMimes = implode(',', config('clinic.uploads.xray.mimes', ['jpeg', 'png', 'jpg', 'webp']));
        $xrayMaxKb = (int) config('clinic.uploads.xray.max_kb', 5120);

        return [
            'tooth_number'   => ['nullable', 'integer', "between:{$toothMin},{$toothMax}"],
            'condition_type' => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string', 'min:5', 'max:2000'],
            'xray_image'     => ['nullable', 'image', "mimes:{$xrayMimes}", "max:{$xrayMaxKb}"],
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
