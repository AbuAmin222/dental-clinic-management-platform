<?php

declare(strict_types=1);

namespace App\Strategies\Profile;

use App\Contracts\Profile\RoleProfileStrategyInterface;
use App\Enums\AdminAccessLevel;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * يطابق بنية FinancialProfileStrategy/ReceptionistProfileStrategy تماماً. `access_level`
 * لا يُقبل أبداً من `$data` الخارجية عمداً — دائماً `AdminAccessLevel::Admin` الافتراضي عند
 * الإنشاء عبر هذا المسار؛ الترقية لـ `SuperAdmin` إجراء منفصل ومتعمَّد حصرياً (لا يجوز أن
 * يصبح أي حساب "مسؤولاً جذرياً" ضمنياً عبر نموذج إنشاء عام).
 */
class AdminProfileStrategy implements RoleProfileStrategyInterface
{
    public function create(User $user, array $data): void
    {
        Admin::create([
            'user_id'         => $user->id,
            'employee_number' => $data['employee_number'],
            'access_level'    => AdminAccessLevel::Admin,
            'hiring_date'     => $data['hiring_date'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ]);
    }

    public function update(User $user, array $data): void
    {
        $admin = Admin::where('user_id', $user->id)->firstOrFail();

        $admin->update(array_filter([
            'employee_number' => $data['employee_number'] ?? $admin->employee_number,
            'hiring_date'     => $data['hiring_date'] ?? $admin->hiring_date,
            'notes'           => $data['notes'] ?? $admin->notes,
        ], fn($value) => $value !== null));
    }

    #[Override]
    public function getProfile(User $user): ?Model
    {
        return $user->profile;
    }

    public function delete(User $user): void
    {
        Admin::where('user_id', $user->id)->delete();
    }
}
