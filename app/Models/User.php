<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Casts\MoneyCast;
use App\Factories\Model\ProfileModelFactory;
use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function App\Helpers\storage_engine;

/**
 * <div dir="rtl">
 * نموذج المستخدم (User Model) — يمثل الكيان المركزي لكل الحسابات في النظام
 * (طبيب، مريض، موظف استقبال، مالي، مدير) بغضّ النظر عن دوره الوظيفي.
 *
 * هذا النموذج هو نقطة الدخول الوحيدة للمصادقة (Authentication)، ولإدارة
 * الأدوار (Roles) والصلاحيات (Permissions) عبر علاقات Many-to-Many مع
 * نماذج وسيطة مخصّصة (Pivot Models)، بالإضافة إلى ربط ديناميكي بجدول
 * "الملف الشخصي" (Profile) المناسب حسب الدور الأساسي للمستخدم.
 * </div>
 *
 * The central User model representing every account in the system
 * (Doctor, Patient, Receptionist, Financial, Admin) regardless of its
 * functional role.
 *
 * This model is the single entry point for authentication, and for
 * managing Roles and Permissions through Many-to-Many relationships
 * backed by custom Pivot models, in addition to dynamically resolving
 * the appropriate "Profile" relation based on the user's primary role.
 *
 * @property int                    $id
 * @property string                 $first_name
 * @property string|null            $middle_name
 * @property string                 $last_name
 * @property string                 $username
 * @property string                 $email
 * @property string|null            $identity_number
 * @property string|null            $phone
 * @property string                 $password
 * @property string|null            $address
 * @property string|null            $identity_photo_path
 * @property string|null            $gender
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property bool                   $is_active
 * @property string|null            $profile_photo_path
 * @property string|null            $phone_verification_code
 * @property \Illuminate\Support\Carbon|null $phone_verification_code_expires_at
 * @property \Illuminate\Support\Carbon|null $phone_verified_at
 * @property bool                   $must_change_password
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed                  $base_salary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string|null       $role                  ملحقة للتوافق الخلفي فقط / Read-only, backward-compat only
 * @property-read string            $full_name
 * @property-read string            $profile_photo_url
 * @property-read string            $identity_photo_url
 * @property-read Doctor|null       $doctor
 * @property-read Patient|null      $patient
 * @property-read Receptionist|null $receptionist
 * @property-read Financial|null    $financial
 * @property-read Admin|null        $admin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role>       $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SalaryPayment> $salaryPayments
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasProfilePhoto,
        TwoFactorAuthenticatable,
        SoftDeletes;


    /** @use HasFactory<\Database\Factories\UserFactory> */

    /*
    |--------------------------------------------------------------------------
    | <div dir="rtl">إعدادات النموذج الأساسية (Mass Assignment / Serialization)</div>
    | Core Model Configuration (Mass Assignment / Serialization)
    |--------------------------------------------------------------------------
    */

    /**
     * <div dir="rtl">
     * الخصائص القابلة للإسناد الجماعي (Mass Assignment) عبر `create()` / `fill()`.
     * </div>
     *
     * The attributes that are mass assignable via `create()` / `fill()`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'username',
        'email',
        'password',
        'must_change_password',
        'identity_number',
        'date_of_birth',
        'phone',
        'phone_verification_code',
        'phone_verification_code_expires_at',
        'phone_verified_at',
        'address',
        'identity_photo_path',
        'profile_photo_path',
        'gender',
        'is_active',
        'is_profile_completed',
        'registration_source',
    ];

    /**
     * <div dir="rtl">
     * الخصائص التي يجب إخفاؤها عند تحويل النموذج إلى مصفوفة/JSON
     * (حماية بيانات حساسة مثل كلمة المرور ورموز التحقق).
     * </div>
     *
     * The attributes that should be hidden for serialization
     * (protects sensitive data such as the password and verification codes).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'phone_verification_code',
    ];

    /**
     * <div dir="rtl">
     * الخصائص الافتراضية (Accessors) التي تُضاف تلقائياً عند تحويل النموذج
     * إلى مصفوفة/JSON — تُستخدم لإرسال روابط URL جاهزة إلى الواجهة الأمامية (Vue).
     * </div>
     *
     * The accessors to append to the model's array/JSON form — used to
     * automatically expose ready-to-use URL fields to the frontend (Vue).
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
        'profile_photo_url',
    ];

    /**
     * <div dir="rtl">
     * تعريف تحويلات الأنواع (Casts) للخصائص.
     * </div>
     *
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'base_salary' => MoneyCast::class,
            'must_change_password' => 'boolean',
            'phone_verification_code_expires_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'is_profile_completed' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | <div dir="rtl">خاصية التوافق الخلفي مع الدور (Legacy Role Accessor)</div>
    | Legacy Role Accessor (Backward Compatibility)
    |--------------------------------------------------------------------------
    */

    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->primaryRole()?->name,
        )->shouldCache();
    }

    /*
    |--------------------------------------------------------------------------
    | <div dir="rtl">علاقات الملفات الشخصية حسب الدور (Role-Specific Profile Relations)</div>
    | Role-Specific Profile Relations
    |--------------------------------------------------------------------------
    */

    /**
     * One-to-one relation with the doctor's profile record.
     *
     * @return HasOne<Doctor, $this>
     */
    public function doctor(): HasOne
    {
        return
            $this->hasOne(Doctor::class);
    }

    /**
     * One-to-one relation with the patient's profile record.
     *
     * @return HasOne<Patient, $this>
     */
    public function patient(): HasOne
    {
        return
            $this->hasOne(Patient::class);
    }

    /**
     * One-to-one relation with the receptionist's profile record.
     *
     * @return HasOne<Receptionist, $this>
     */
    public function receptionist(): HasOne
    {
        return
            $this->hasOne(Receptionist::class);
    }

    /**
     * One-to-one relation with the financial (accounting) profile record.
     *
     * @return HasOne<Financial, $this>
     */
    public function financial(): HasOne
    {
        return
            $this->hasOne(Financial::class);
    }

    /**
     * One-to-one relation with the admin's profile record.
     *
     * @return HasOne<Admin, $this>
     */
    public function admin(): HasOne
    {
        return
            $this->hasOne(Admin::class);
    }

    /**
     * <div dir="rtl">
     * يحلّ ديناميكياً علاقة "الملف الشخصي" الفعلية بناءً على الدور الأساسي
     * الحالي للمستخدم (وقت التنفيذ)، عبر {@see \App\Factories\Model\ProfileModelFactory}.
     * القيمة الافتراضية عند عدم وجود دور أو نوع ملف هي `patient`.
     * </div>
     *
     * Dynamically resolve the concrete profile relation based on the runtime
     * user role, via {@see \App\Factories\Model\ProfileModelFactory}.
     * Defaults to `patient` when no role or profile type is resolvable.
     *
     * @return HasOne<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function profile(): HasOne
    {
        $role = $this->primaryRole();

        $profileKey = $role?->profile_type ?? $role?->name ?? 'patient';

        return $this->hasOne(
            ProfileModelFactory::resolveClass($profileKey),
            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | <div dir="rtl">جداول العلاقات الوسيطة (Pivot Relations): الأدوار والصلاحيات</div>
    | Pivot Relations (Roles & Permissions)
    |--------------------------------------------------------------------------
    */

    /**
     * <div dir="rtl">
     * جلب الأدوار المُسندة للمستخدم.
     *
     * تُعرّف علاقة متعدد-إلى-متعدد (Many-to-Many) باستخدام نموذج وسيط مخصص
     * للوصول إلى الخصائص الإضافية في جدول الوسيط مثل `is_primary`.
     * جدول العلاقة: `users` × `roles` => `role_users` (Pivot Table).
     * </div>
     *
     * Get the roles assigned to the user.
     *
     * Defines a many-to-many relationship using a custom pivot model
     * to access extended intermediate attributes like `is_primary`.
     * Relation table: `users` × `roles` => `role_users` (Pivot Table).
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_users')
            ->using(RoleUser::class)
            ->withPivot('is_primary');
    }

    /**
     * <div dir="rtl">
     * جلب الصلاحيات المباشرة الممنوحة للمستخدم (بمعزل عن صلاحيات دوره).
     *
     * تُعرّف علاقة متعدد-إلى-متعدد باستخدام نموذج وسيط مخصص لتتبع المُشرف
     * المانح للصلاحية وتوثيق الطوابع الزمنية للإسناد.
     * جدول العلاقة: `users` × `permissions` => `user_permissions` (Pivot Table).
     * </div>
     *
     * Get the direct permissions assigned to the user.
     *
     * Defines a many-to-many relationship using a custom pivot model
     * to audit who granted the permission and track assignment timestamps.
     * Relation table: `users` × `permissions` => `user_permissions` (Pivot Table).
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->using(UserPermission::class)
            ->withPivot('granted_by')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | <div dir="rtl">دوال جاهزة ومساعدة للتعامل مع الأدوار والصلاحيات (Helper API)</div>
    | Roles & Permissions Helper API
    |--------------------------------------------------------------------------
    */

    /**
     * <div dir="rtl">
     * إسناد دور للمستخدم.
     *
     * أول دور يُسند لمستخدم جديد يصبح أساسياً (Primary) تلقائياً، حتى لو لم يُطلب
     * ذلك صراحة — لأن مستخدماً بلا دور أساسي يعني أن
     * {@see User::primaryRole()} / {@see User::profile()} ستفشل بصمت.
     * </div>
     *
     * Assign a role to the user.
     *
     * The first role assigned to a new user automatically becomes primary,
     * even if not explicitly requested — because a user with no primary role
     * means {@see User::primaryRole()} / {@see User::profile()} will fail silently.
     *
     * @param  Role|string $role      <div dir="rtl">اسم الدور أو نموذج الدور / Role name or Role model instance</div>
     * @param  bool        $isPrimary <div dir="rtl">هل يُجعل هذا الدور أساسياً؟ / Whether this role should become primary</div>
     * @return $this
     */
    public function assignRole(Role|string $role, bool $isPrimary = false): self
    {
        $roleModel = is_string($role)
            ? Role::where('name', $role)->firstOrFail()
            : $role;

        $isPrimary = $isPrimary || $this->roles()->doesntExist();

        if ($isPrimary) {
            $this->roles()->updateExistingPivot(
                $this->roles()->pluck('roles.id'),
                ['is_primary' => false]
            );
        }

        $this->roles()->syncWithoutDetaching([
            $roleModel->id => ['is_primary' => $isPrimary]
        ]);

        return $this;
    }

    /**
     * <div dir="rtl">سحب دور من المستخدم.</div>
     *
     * Remove a role from the user.
     *
     * @param  Role|string $role <div dir="rtl">اسم الدور أو نموذج الدور / Role name or Role model instance</div>
     * @return $this
     */
    public function removeRole(Role|string $role): self
    {
        $roleModel = is_string($role)
            ? Role::where('name', $role)->first()
            : $role;

        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
        }

        return $this;
    }

    /**
     * <div dir="rtl">
     * منح صلاحية مباشرة لهذا المستخدم، بمعزل عن صلاحيات دوره.
     * </div>
     *
     * Grant a direct permission to this user, independent of their role's permissions.
     *
     * @param  Permission|string $permission <div dir="rtl">اسم الصلاحية أو نموذجها / Permission name or Permission model instance</div>
     * @param  User|null         $grantedBy  <div dir="rtl">المستخدم المانح للصلاحية (للتدقيق) / The granting user, for audit purposes</div>
     * @return $this
     */
    public function givePermissionTo(Permission|string $permission, ?User $grantedBy = null): self
    {
        $permissionModel = is_string($permission)
            ? Permission::where('name', $permission)
            ->firstOrFail()
            : $permission;

        $this->permissions()
            ->syncWithoutDetaching([
                $permissionModel->id => ['granted_by' => $grantedBy?->id],
            ]);

        return $this;
    }

    /**
     * <div dir="rtl">
     * سحب صلاحية مباشرة من هذا المستخدم (لا تؤثر على صلاحيات دوره).
     * </div>
     *
     * Revoke a direct permission from this user (does not affect role-based permissions).
     *
     * @param  Permission|string $permission <div dir="rtl">اسم الصلاحية أو نموذجها / Permission name or Permission model instance</div>
     * @return $this
     */
    public function revokePermissionTo(Permission|string $permission): self
    {
        $permissionModel = is_string($permission)
            ? Permission::where('name', $permission)->first()
            : $permission;

        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
        }

        return $this;
    }

    /**
     * <div dir="rtl">
     * جلب الدور الأساسي (Primary Role) للمستخدم.
     *
     * تُفضَّل العلاقة المحمَّلة مسبقاً (Eager-Loaded) لتفادي استعلامات N+1؛
     * وفي حال عدم وجود دور مُعلَّم كأساسي صراحة، يُستخدم أول دور مُسند كبديل.
     * </div>
     *
     * Fetch the user's primary role.
     *
     * Prefers the eager-loaded relation to avoid N+1 queries; when no role
     * is explicitly flagged as primary, falls back to the first assigned role.
     *
     * @return Role|null
     */
    public function primaryRole(): ?Role
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->firstWhere('pivot.is_primary', true)
                ?? $this->roles->first();
        }

        return $this->roles()->wherePivot('is_primary', true)->first()
            ?? $this->roles()->first();
    }

    /**
     * <div dir="rtl">
     * التحقق هل يملك المستخدم دوراً معيناً (أو أحد الأدوار الممرَّرة).
     * </div>
     *
     * Check whether the user has a given role (or any of the given roles).
     *
     * @param  string|array<int, string> $roles <div dir="rtl">اسم دور واحد أو مصفوفة أسماء أدوار / A single role name or an array of role names</div>
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : func_get_args();

        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    /**
     * <div dir="rtl">
     * التحقق هل يملك المستخدم صلاحية معينة (مباشرة أو عبر أحد أدواره).
     *
     * ترتيب الفحص: (1) مسؤول النظام (Admin) يتجاوز الفحص وله كافة الصلاحيات،
     * (2) صلاحية ممنوحة مباشرة للمستخدم نفسه، (3) صلاحية ممنوحة عبر أحد أدواره.
     * </div>
     *
     * Check whether the user has a given permission (directly or via any of their roles).
     *
     * Check order: (1) a system Admin bypasses the check and has every
     * permission, (2) a permission granted directly to the user,
     * (3) a permission granted through one of the user's roles.
     *
     * @param  string $permission <div dir="rtl">اسم الصلاحية المراد التحقق منها / The permission name to check</div>
     * @return bool
     */
    public function hasPermissionTo(string $permission): bool
    {
        // إذا كان مسؤول نظام (Admin) يتجاوز الفحص وله كافة الصلاحيات
        if ($this->hasRole('admin')) {
            return true;
        }

        // مسار 1: صلاحية ممنوحة مباشرة للمستخدم نفسه (تجاوز دوره)
        if ($this->permissions->contains('name', $permission)) {
            return true;
        }

        // مسار 2: صلاحية ممنوحة عبر أحد أدوار المستخدم
        return $this->roles->flatMap(fn(Role $role) => $role->permissions)
            ->contains('name', $permission);
    }

    /**
     * <div dir="rtl">
     * جلب كل أسماء الصلاحيات "الفعّالة" للمستخدم (مباشرة + عبر أدواره)،
     * بعد إزالة التكرار. مسؤول النظام (Admin) يحصل على كل الصلاحيات المعرَّفة
     * في النظام دون استثناء.
     * </div>
     *
     * Get all "effective" permission names for the user (direct + via roles),
     * deduplicated. A system Admin receives every permission defined in the
     * system without exception.
     *
     * @return array<int, string>
     */
    public function effectivePermissionNames(): array
    {
        if ($this->hasRole('admin')) {
            return Permission::pluck('name')->all();
        }

        $direct = $this->permissions->pluck('name');
        $viaRoles = $this->roles->flatMap(fn(Role $role) => $role->permissions->pluck('name'));

        return $direct->concat($viaRoles)->unique()->values()->all();
    }

    /*
    |--------------------------------------------------------------------------
    | <div dir="rtl">علاقات إضافية (Additional Relations)</div>
    | Additional Relations
    |--------------------------------------------------------------------------
    */

    /**
     * <div dir="rtl">دفعات الراتب الخاصة بالمستخدم.</div>
     *
     * The salary payments belonging to the user.
     *
     * @return HasMany<SalaryPayment, $this>
     */
    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | <div dir="rtl">خصائص افتراضية (Accessors): الصور والاسم الكامل</div>
    | Accessors: Photos & Full Name
    |--------------------------------------------------------------------------
    */

    /**
     * <div dir="rtl">
     * جلب رابط URL لصورة الملف الشخصي للمستخدم (نمط قديم / Legacy Attribute).
     * </div>
     *
     * Get the URL to the user's profile photo (legacy attribute style).
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return
                asset("storage/{$this->profile_photo_path}");
        }

        // Fallback UI default initials avatar if no photo exists
        $name = urlencode($this->first_name . ' ' . $this->last_name);
        return
            "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF";
    }

    /**
     * <div dir="rtl">
     * جلب رابط URL لصورة الملف الشخصي مع رجوع تلقائي (Fallback) إلى صورة
     * أحرف اسم افتراضية عبر خدمة UI-Avatars عند عدم وجود صورة مرفوعة.
     * (نمط حديث / Modern Attribute Casting).
     * </div>
     *
     * Get the URL to the user's profile photo with automatic UI-Avatar
     * initials fallback when no photo has been uploaded.
     * (Modern Attribute Casting style).
     *
     * @return Attribute<string, never>
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => storage_engine()->url(
                $this->profile_photo_path,
                'public',
                'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&color=7F9CF5&background=EBF4FF'
            )
        );
    }

    /**
     * <div dir="rtl">
     * جلب رابط URL لصورة الوثيقة الرسمية (الهوية) الخاصة بالمستخدم،
     * مع رجوع تلقائي (Fallback) إلى صورة نائبة عند عدم وجود مستند مرفوع.
     * </div>
     *
     * Get the URL to the user's official identity photo/document,
     * with an automatic placeholder fallback when no document has been uploaded.
     *
     * @return Attribute<string, never>
     */
    protected function identityPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => storage_engine()->url(
                $this->identity_photo_path,
                'public',
                // Fallback placeholder graphic if no document uploaded
                'https://placehold.co/600x400?text=No+Identity+Document'
            )
        );
    }

    /**
     * <div dir="rtl">
     * جلب الاسم الكامل للمستخدم (نمط قديم / Legacy Attribute — تم الإبقاء عليه
     * للتوافق الخلفي؛ يُفضَّل استخدام {@see User::fullName()} في الكود الجديد).
     * </div>
     *
     * Keep existing full name accessor (legacy style — kept for backward
     * compatibility; new code should prefer {@see User::fullName()}).
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->middle_name} {$this->last_name}";
    }

    /**
     * <div dir="rtl">
     * الاسم الكامل للمستخدم (نمط حديث / Modernized Attribute Casting)،
     * مع تقليم (trim) أي فراغات زائدة ناتجة عن غياب `middle_name`.
     * </div>
     *
     * Modernized Full Name Attribute Accessor, trimming any extra
     * whitespace produced by a missing `middle_name`.
     *
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->middle_name} {$this->last_name}")
        );
    }

    /*
    |--------------------------------------------------------------------------
    | <div dir="rtl">تجاوزات الإشعارات (Notification Overrides)</div>
    | Notification Overrides
    |--------------------------------------------------------------------------
    */

    /**
     * <div dir="rtl">
     * إرسال إشعار التحقق من البريد الإلكتروني عبر قائمة انتظار (Queue) مخصصة
     * بدلاً من الإشعار الافتراضي المتزامن الذي يوفره Laravel.
     * </div>
     *
     * Send the email verification notification through a dedicated queued
     * notification instead of Laravel's default synchronous notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail());
    }

    /**
     * <div dir="rtl">
     * إرسال إشعار إعادة تعيين كلمة المرور عبر قائمة انتظار (Queue) مخصصة
     * بدلاً من الإشعار الافتراضي المتزامن الذي يوفره Laravel.
     * </div>
     *
     * Send the password reset notification through a dedicated queued
     * notification instead of Laravel's default synchronous notification.
     *
     * @param  string $token <div dir="rtl">رمز إعادة التعيين / The password reset token</div>
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }

    /**
     * <div dir="rtl">
     * تحديد رقم الهاتف المستخدَم لتوجيه الإشعارات عبر قناة `sms` المخصصة
     * ({@see \App\Notifications\Channels\SmsChannel}) — تطبيق لاتفاقية Laravel القياسية
     * `routeNotificationFor{Channel}`، تماماً مثل `routeNotificationForMail()` المدمجة.
     * </div>
     *
     * Resolve the phone number used to route notifications through the custom `sms`
     * channel ({@see \App\Notifications\Channels\SmsChannel}) — implements Laravel's
     * standard `routeNotificationFor{Channel}` convention, exactly like the built-in
     * `routeNotificationForMail()`.
     *
     * @param  \Illuminate\Notifications\Notification $notification
     * @return string|null
     */
    public function routeNotificationForSms(\Illuminate\Notifications\Notification $notification): ?string
    {
        return $this->phone;
    }
}
