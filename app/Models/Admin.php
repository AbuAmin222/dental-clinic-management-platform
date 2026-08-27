<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdminAccessLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Admin
 *
 * ملف تعريف المسؤول — يكمل تناظر الأدوار الخمسة (Doctor/Patient/Receptionist/Financial/Admin)،
 * كلها بنفس البنية والاصطلاحات (`ProfileModelFactory::resolveClass('admin')` يحلّها تلقائياً
 * عبر `Str::studly('admin')` بدون أي تعديل على الـ Factory، تماماً كبقية الأدوار).
 *
 * @property int $id
 * @property int $user_id
 * @property string $employee_number
 * @property AdminAccessLevel $access_level
 * @property \Carbon\Carbon|null $hiring_date
 * @property \Carbon\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property string|null $notes
 *
 * @property-read \App\Models\User $user
 */
class Admin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_number',
        'access_level',
        'hiring_date',
        'notes',
    ];

    protected $casts = [
        'access_level'   => AdminAccessLevel::class,
        'hiring_date'    => 'date',
        'last_login_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * الصلاحيات التي منحها هذا المسؤول مباشرة لمستخدمين (مسار المنح الفردي — انظر
     * User::givePermissionTo()، عمود `granted_by` في جدول user_permissions).
     *
     * ليست علاقة Eloquent قياسية (hasMany/belongsTo) عمداً: `user_permissions.granted_by`
     * يشير إلى `users.id` وليس `admins.id` — أي `hasMany` هنا كان سيكون تحايلاً مضلِّلاً
     * على القارئ لاحقاً. استعلام Query Builder صريح أوضح وأصدق تقنياً.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Permission>
     */
    public function grantedUserPermissions()
    {
        return Permission::query()
            ->join('user_permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.granted_by', $this->user_id)
            ->select('permissions.*', 'user_permissions.user_id as granted_to_user_id', 'user_permissions.created_at as granted_at')
            ->get();
    }

    public function isSuperAdmin(): bool
    {
        return $this->access_level === AdminAccessLevel::SuperAdmin;
    }

    /**
     * يُستدعى عند تسجيل دخول ناجح لتحديث بصمة الأمان الخاصة بحساب المسؤول (وقت/عنوان IP
     * آخر دخول) — قيمة تدقيقية مهمة لحساب بصلاحيات هرمية كاملة. استدعها من أي Listener
     * على حدث Illuminate\Auth\Events\Login مستقبلاً؛ لم تُربَط تلقائياً بعد (خارج نطاق
     * إنشاء الجدول نفسه، يحتاج تسجيل Listener في EventServiceProvider).
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ])->save();
    }
}
