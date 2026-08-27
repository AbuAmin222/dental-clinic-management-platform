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

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasProfilePhoto,
        TwoFactorAuthenticatable,
        SoftDeletes;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'username',
        'email',
        'identity_number',
        'phone',
        'password',
        'address',
        'identity_photo_path',
        'gender',
        'date_of_birth',
        'is_active',
        'profile_photo_path',
        'phone_verification_code',
        'phone_verification_code_expires_at',
        'phone_verified_at',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
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
     * The accessors to append to the model's array form.
     *Automatically append virtual URL fields whenever this model is sent to Vue.
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
        'profile_photo_url',
    ];

    /**
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
        ];
    }

    /**
     * Role/Permission adoption — FULL removal of `users.role` (2026-08-11):
     *
     * `users.role` no longer exists as a physical column at all; `roles`/`role_users` is now
     * the only place a user's role is stored. This read-only accessor exists SOLELY so the
     * many existing call sites across the app (`$user->role === 'financial'`,
     * `in_array($user->role, [...])`, `match ($user->role) {...}`) keep working without being
     * individually rewritten — property access on an Eloquent Model always goes through this
     * accessor now, since there is no real column left for PHP to fall back to. Any NEW code
     * should prefer `hasRole()` directly rather than string-comparing this accessor's output.
     *
     * This is read-only on purpose: `$user->role = 'doctor'` will NOT persist anything (no
     * column backs it) — assigning a role MUST go through `assignRole()`, which is now the
     * single real write path with no denormalized column left to keep in sync.
     */
    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn(): ?string => $this->primaryRole()?->name,
        )->shouldCache();
    }

    public function doctor(): HasOne
    {
        return
            $this->hasOne(Doctor::class);
    }

    public function patient(): HasOne
    {
        return
            $this->hasOne(Patient::class);
    }

    public function receptionist(): HasOne
    {
        return
            $this->hasOne(Receptionist::class);
    }

    public function financial(): HasOne
    {
        return
            $this->hasOne(Financial::class);
    }

    public function admin(): HasOne
    {
        return
            $this->hasOne(Admin::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_users')
            ->using(RoleUser::class)
            ->withPivot('is_primary');
    }

    /**
     * الصلاحيات الممنوحة مباشرة لهذا المستخدم (بمعزل عن دوره) — مسار المنح الثاني
     * المؤكد: "المسؤول يعطي الصلاحية التي يريدها لأي موظف أو دور".
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->using(UserPermission::class)
            ->withPivot('granted_by')
            ->withTimestamps();
    }

    /**
     * منح صلاحية مباشرة لهذا المستخدم، بمعزل عن صلاحيات دوره.
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
     * سحب صلاحية مباشرة من هذا المستخدم (لا تؤثر على صلاحيات دوره).
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
     * Fetch main role for user.
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<SalaryPayment>
     */
    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Dynamically resolve the concrete profile relation based on the runtime user role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
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

    /**
     * Get the URL to the user's profile photo.
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
     * Get the URL to the user's profile photo with automatic UI-Avatar fallback.
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
     * Get the URL to the user's official identity photo/document.
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
     * Keep existing full name accessor.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->middle_name} {$this->last_name}";
    }
    /**
     * Modernized Full Name Attribute Accessor.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->middle_name} {$this->last_name}")
        );
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }

    /*
    |--------------------------------------------------------------------------
    | دوال جاهزة ومساعدة للتعامل مع الأدوار والصلاحيات (Helper API)
    |--------------------------------------------------------------------------
    */

    /**
     * إسناد دور للمستخدم
     *
     * `roles`/`role_users` هو المصدر الوحيد للحقيقة — لا يوجد عمود `users.role` فيزيائي
     * إطلاقاً بعد الآن (أُزيل بالكامل). القراءة القديمة (`$user->role === 'financial'`)
     * تستمر بالعمل تلقائياً عبر الـ accessor المُعرَّف أعلاه فقط — لا يوجد أي تخزين فعلي
     * مرتبط بالاسم. أي كود جديد يجب أن يستخدم `hasRole()`/`assignRole()` مباشرة.
     */
    public function assignRole(Role|string $role, bool $isPrimary = false): self
    {
        $roleModel = is_string($role)
            ? Role::where('name', $role)->firstOrFail()
            : $role;

        // أول دور يُسند لمستخدم جديد يكون أساسياً تلقائياً، حتى لو لم يُطلب ذلك صراحة —
        // مستخدم بلا دور أساسي يعني primaryRole()/profile() ستفشل بصمت.
        $isPrimary = $isPrimary || $this->roles()->doesntExist();

        if ($isPrimary) {
            // إلغاء الفاعلية عن باقي الأدوار كدور رئيسي
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
     * سحب دور من المستخدم
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
     * التحقق هل يملك المستخدم دوراً معيناً (أو أحد الأدوار الممررة)
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : func_get_args();

        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    /**
     * التحقق هل يملك المستخدم صلاحية معينة (مباشرة أو عبر أحد أدوراه)
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

    public function effectivePermissionNames(): array
    {
        if ($this->hasRole('admin')) {
            return Permission::pluck('name')->all();
        }

        $direct = $this->permissions->pluck('name');
        $viaRoles = $this->roles->flatMap(fn(Role $role) => $role->permissions->pluck('name'));

        return $direct->concat($viaRoles)->unique()->values()->all();
    }
}
