<?php

namespace App\Strategies\Profile;

use App\Contracts\Profile\RoleProfileStrategyInterface;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Model;
use Override;

class DoctorProfileStrategy implements RoleProfileStrategyInterface
{
    /**
     * Create a new doctor profile record with verified specialization mapping.
     *
     * @param  \App\Models\User  $user
     * @param  array<string, mixed>  $data
     * @return void
     */
    public function create(User $user, array $data): void
    {
        $specialization = Specialization::findOrFail($data['specialization_id'], ['id']);

        Doctor::create([
            'user_id' => $user->id,
            'specialization_id' => $specialization->id,
            'license_number' => $data['license_number'],
            'bio' => $data['bio'] ?? null,
            'experience_years' => $data['experience_years'] ?? 0,
        ]);
    }

    /**
     * Update the doctor profile record and update foreign constraints if provided.
     *
     * @param  \App\Models\User  $user
     * @param  array<string, mixed>  $data
     * @return void
     */
    public function update(User $user, array $data): void
    {
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        $doctor->update(array_filter([
            'specialization_id' => $data['specialization_id'] ?? $doctor->specialization_id,
            'license_number' => $data['license_number'] ?? $doctor->license_number,
            'bio' => $data['bio'] ?? $doctor->bio,
            'experience_years' => $data['experience_years'] ?? $doctor->experience_years,
        ], fn($value) => $value !== null));
    }

    #[Override]
    public function getProfile(User $user): ?Model
    {
        return $user->profile;
    }

    /**
     * Delete the doctor profile record linked to the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function delete(User $user): void
    {
        Doctor::where('user_id', $user->id)->delete();
    }
}
