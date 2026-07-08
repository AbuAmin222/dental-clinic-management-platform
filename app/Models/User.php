<?php

namespace App\Models;

use App\Notifications\QueuedResetPassword;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasProfilePhoto,
        TwoFactorAuthenticatable,
        SoftDeletes;

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
     * Get the URL to the user's profile photo with automatic UI-Avatar fallback.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => storage_engine()->url(
                $this->profile_photo_path,
                'public',
                'https://ui-avatars.com/api/?name=' . urlencode($this->fullName) . '&color=7F9CF5&background=EBF4FF'
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
                'https://placehold.co/600x400?text=No+Identity+Document'
            )
        );
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
        // تم التأكيد على استخدام Queue متوافق مع Redis لتفادي بطء الاستجابة في الإنتاج
        $this->notify(new QueuedVerifyEmail());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPassword($token));
    }
}
