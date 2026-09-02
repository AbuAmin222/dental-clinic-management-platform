<?php

namespace App\Strategies\Profile;

use App\Contracts\Profile\CoreProfileStrategyInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CoreProfileStrategy implements CoreProfileStrategyInterface
{
    /**
     * Create a new core data profile record with verified specialization mapping.
     *
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function create(array $data, ?string $profilePath = null, ?string $identityPath = null, ?bool $mustChangePass = true): User
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'identity_number' => $data['identity_number'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'address' => $data['address'],
            'identity_photo_path' => $identityPath,
            'profile_photo_path' => $profilePath,
            'must_change_password' => $mustChangePass,
        ]);

        $user->assignRole($data['role'], isPrimary: true);

        return $user;
    }

    /**
     * Update the doctor profile record and update foreign constraints if provided.
     *
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function update(User $user, array $data, ?string $profilePath = null, ?string $identityPath = null): User
    {
        $updateData = array_filter([
            'first_name'          => $data['first_name'] ?? $user->first_name,
            'middle_name'         => $data['middle_name'] ?? $user->middle_name,
            'last_name'           => $data['last_name'] ?? $user->last_name,
            'username'            => $data['username'] ?? $user->username,
            'email'               => $data['email'] ?? $user->email,
            'identity_number'     => $data['identity_number'] ?? $user->identity_number,
            'phone'               => $data['phone'] ?? $user->phone,
            'gender'              => $data['gender'] ?? $user->gender,
            'date_of_birth'       => $data['date_of_birth'] ?? $user->date_of_birth,
            'address'             => $data['address'] ?? $user->address,
            'identity_photo_path' => $identityPath ?? $user->identity_photo_path,
            'profile_photo_path'  => $profilePath ?? $user->profile_photo_path,
        ], fn($value) => $value !== null);

        if (isset($data['password']) && filled($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user;
    }


    /**
     * Delete the doctor profile record linked to the user.
     *
     * @return void
     */
    public function delete(User $user): void
    {
        $user->delete();
    }
}
