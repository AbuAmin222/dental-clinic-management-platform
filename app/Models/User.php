<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

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
        'role',
        'gender',
        'date_of_birth',
        'is_active',
        'profile_photo_path',
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
    ];

    /**
     * The accessors to append to the model's array form.
     *Automatically append virtual URL fields whenever this model is sent to Vue.
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
        'identity_photo_path',
        'profile_photo_path',
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
        ];
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

    /**
     * Dynamically resolve the concrete profile relation based on the runtime user role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(
            ProfileModelFactory::resolveClass($this->role),
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
            // Automatically maps to public storage symlink: /storage/uploads/{Role}/profiles/{filename}
            return
                asset("storage/{$this->profile_photo_path}");
            // asset("storage/uploads/{$this->role}/profiles/{$this->profile_photo_path}");
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
                // Fallback UI Initials Avatar if no photo exists
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
}
