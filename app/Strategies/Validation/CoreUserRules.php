<?php

declare(strict_types=1);

namespace App\Strategies\Validation;

use App\Enums\Gender;
use Illuminate\Validation\Rule;

class CoreUserRules
{
    /**
     * قواعد رفع صورة الهوية/الملف الشخصي — نقطة واحدة مركزية بدل تكرارها 4 مرات
     * (كانت مكتوبة يدوياً في كل من identity_photo/profile_photo/photo عبر دالتين).
     *
     * @return array<int, mixed>
     */
    private static function identityImageRule(bool $required): array
    {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:' . implode(',', config('clinic.uploads.identity_document.mimes')),
            'max:' . config('clinic.uploads.identity_document.max_kb'),
        ];
    }

    /**
     * Common rules for the registration process (creating a new account).
     */
    public static function getRegistrationRules(): array
    {
        return [
            'first_name'      => ['required', 'string', 'min:3', 'max:20'],
            'middle_name'     => ['required', 'string', 'min:3', 'max:20'],
            'last_name'       => ['required', 'string', 'min:3', 'max:20'],
            'username'        => ['required', 'string', 'min:3', 'max:25', Rule::unique('users', 'username')],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password'        => ['required', 'string', 'min:10', 'max:35'],
            'identity_number' => ['required', 'string', 'size:9', Rule::unique('users', 'identity_number')],
            'phone'           => ['required', 'string', 'regex:' . config('clinic.validation.phone_regex')],
            'gender'          => ['required', Rule::in(Gender::values())],
            'date_of_birth'   => ['required', 'date'],
            'address'         => ['nullable', 'string', 'max:255'],
            'identity_photo'  => self::identityImageRule(required: true),
            'profile_photo'   => self::identityImageRule(required: false),
        ];
    }

    /**
     * Error messages specific to the registration process.
     */
    public static function getRegistrationMessages(): array
    {
        return [
            'first_name.required'      => 'First name required for personal identity account.',
            'first_name.min'           => 'The first name must be at least 3 letters long.',
            'middle_name.required'     => 'Fathers name is a mandatory field.',
            'last_name.required'       => 'The family name is a mandatory field.',
            'username.required'        => 'A unique username is required to access the panel.',
            'username.unique'          => 'This username is already taken in the system.',
            'email.required'           => 'Email is required to verify and activate the account.',
            'email.unique'             => 'This email address is already registered with us.',
            'password.required'           => 'Password is required.',
            'password.min'           => 'The password must be at least 10 letters long.',
            'password.max'           => 'The password must be not greater than 35 letters long.',
            'identity_number.required' => 'Official identification number (9 digits) is required for record security.',
            'identity_number.size'     => 'The national identity number must consist of exactly 9 digits.',
            'identity_number.unique'   => 'This ID number is already in use and registered in the system.',
            'phone.regex'              => 'The phone number format is invalid (it must start with 059 or 056 and be followed by 7 digits).',
            'gender.in'                => 'The specified gender is invalid.',
            'date_of_birth.required'   => 'Date of birth is required to calculate medical age and allocate therapy sessions.',
            'identity_photo.required'  => 'A copy of the official ID is required for the administrative verification process.',
            'identity_photo.image'     => 'A valid photo file must be uploaded to verify identity.',
        ];
    }

    /**
     * Common rules for the update process (profile modification).
     */
    public static function getUpdateRules(int $userId): array
    {
        return [
            'first_name'     => ['required', 'string', 'min:3', 'max:20'],
            'middle_name'    => ['required', 'string', 'min:3', 'max:20'],
            'last_name'      => ['required', 'string', 'min:3', 'max:20'],
            'username'       => ['required', 'string', 'min:3', 'max:25', Rule::unique('users', 'username')->ignore($userId)],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone'          => ['required', 'string', 'regex:' . config('clinic.validation.phone_regex')],
            'gender'         => ['required', Rule::in(Gender::values())],
            'date_of_birth'  => ['required', 'date'],
            'address'        => ['nullable', 'string', 'max:255'],
            'identity_photo' => self::identityImageRule(required: false),
            'photo'          => self::identityImageRule(required: false),
        ];
    }

    /**
     * Error messages specific to the update process.
     */
    public static function getUpdateMessages(): array
    {
        return self::getRegistrationMessages();
    }
}
