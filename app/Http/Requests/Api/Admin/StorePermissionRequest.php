<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin->value) ?? false;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:150', 'unique:permissions,name', 'regex:/^[a-z0-9_.\-]+$/'],
            'display_name' => ['required', 'string', 'max:150'],
            'group'        => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'اسم الصلاحية يجب أن يكون بصيغة برمجية (حروف صغيرة، أرقام، نقطة، شرطة سفلية فقط) — مثال: invoices.approve',
        ];
    }
}
