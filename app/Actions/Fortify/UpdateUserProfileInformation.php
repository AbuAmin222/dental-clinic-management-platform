<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'first_name'    => ['required', 'string', 'min:3', 'max:20'],
            'middle_name'   => ['required', 'string', 'min:3', 'max:20'],
            'last_name'     => ['required', 'string', 'min:3', 'max:20'],
            'username'      => ['required', 'string', 'min:3', 'max:25'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'         => ['required', 'string', 'regex:/^(059|056)\d{7}$/'],
            'gender'        => ['required', 'in:Male,Female'],
            'date_of_birth' => ['required', 'date'],
            'address'       => ['nullable', 'string', 'max:255'],

            'photo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'identity_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ])->validateWithBag('updateProfileInformation');

        $updatedAttributes = [
            'first_name'    => $input['first_name'],
            'middle_name'   => $input['middle_name'],
            'last_name'     => $input['last_name'],
            'username'      => $input['username'],
            'phone'         => $input['phone'],
            'gender'        => $input['gender'],
            'date_of_birth' => $input['date_of_birth'],
            'address'       => $input['address'],
        ];

        $name = $updatedAttributes['first_name'] . ' ' . $updatedAttributes['last_name'];
        $roleDir = strtolower($user->role);

        if (isset($input['photo']) && $input['photo'] instanceof UploadedFile) {
            $profileOld = $user->profile_photo_path;
            $profileFile = $input['photo'];

            $updatedAttributes['profile_photo_path'] = storage_engine()->update($name, $profileFile, $profileOld, "uploads/{$roleDir}/profiles");
        }

        if (isset($input['identity_photo']) && $input['identity_photo'] instanceof UploadedFile) {
            $identityOld = $user->identity_photo_path;
            $identityFile = $input['identity_photo'];


            $updatedAttributes['identity_photo_path'] = storage_engine()->update(
                $name,
                $identityFile,
                $identityOld,
                "secure/{$roleDir}/identities",
                'local' // تمرير اسم الـ Private Disk للمحرك المركزي الخاص بك
            );
        }

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $updatedAttributes['email'] = $input['email'];
            $updatedAttributes['email_verified_at'] = null;

            $user->forceFill($updatedAttributes)->save();
            $user->sendEmailVerificationNotification();
        } else {
            $updatedAttributes['email'] = $input['email'];
            $user->forceFill($updatedAttributes)->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'first_name'        => $input['first_name'],
            'middle_name'       => $input['middle_name'],
            'last_name'         => $input['last_name'],
            'username'          => $input['username'],
            'email'             => $input['email'],
            'phone'             => $input['phone'],
            'gender'            => $input['gender'],
            'date_of_birth'     => $input['date_of_birth'],
            'address'           => $input['address'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
