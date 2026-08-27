<?php

declare(strict_types=1);

namespace App\Strategies\Validation;

use App\Contracts\Validation\RoleValidationRulesInterface;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * ملاحظة معمارية:
 *  لا يوجد نموذج تسجيل عام لدور Admin (ولا يجوز أن يوجد — إتاحة تسجيل
 * "مسؤول نظام" من نموذج تسجيل عمومي ثغرة أمنية جسيمة).
 *  حسابات Admin تُنشأ حصراً عبر
 * Seeder/Tinker مباشرة أو بواسطة مسؤول جذري (SuperAdmin) موجود مسبقاً.
 *  هذا الكلاس موجود فقط لاكتمال التناظر المعماري مع بقية الأدوار عبر
 *  RoleValidationFactory (بحيث لا يفشل`RoleValidationFactory::make('admin')` إن استُدعي مستقبلاً)، وليس مربوطاً بأي Route
 * عام حالياً.
 */
class AdminValidationRules implements RoleValidationRulesInterface
{
    public function getRegistrationRules(): array
    {
        return [
            'employee_number' => ['required', 'string', Rule::unique('admins', 'employee_number')],
            'hiring_date'      => ['nullable', 'date', 'before_or_equal:today'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function getUpdateRules(User $user, array $input): array
    {
        return [
            'employee_number'  => ['required', 'string', Rule::unique('admins', 'employee_number')->ignore($user->admin?->id)],
            'hiring_date'      => ['nullable', 'date', 'before_or_equal:today'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_number.required' => 'The official institutional employee identification number is required.',
            'employee_number.unique'   => 'This employee identification number is already assigned to another staff member.',
            'hiring_date.before_or_equal' => 'The hiring date cannot be in the future.',
        ];
    }
}
