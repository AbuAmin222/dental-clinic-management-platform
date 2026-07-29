<?php

namespace App\Strategies\Profile;

use App\Contracts\Profile\RoleProfileStrategyInterface;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Override;

class PatientProfileStrategy implements RoleProfileStrategyInterface
{
    /**
     * Create a new patient profile record associated with the base user.
     *
     * @param  \App\Models\User  $user
     * @param  array<string, mixed>  $data
     * @return void
     */
    public function create(User $user, array $data): void
    {
        Patient::create([
            'user_id' => $user->id,
            'blood_group' => $data['blood_group'],
            'allergies' => $data['allergies'] ?? null,
            'chronic_diseases' => $data['chronic_diseases'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'],
            'emergency_contact_phone' => $data['emergency_contact_phone'],
        ]);
    }

    /**
     * Update the patient profile record associated with the given user.
     *
     * @param  \App\Models\User  $user
     * @param  array<string, mixed>  $data
     * @return void
     */
    public function update(User $user, array $data): void
    {
        $patient = Patient::where('user_id', $user->id)->firstOrFail();

        $patient->update(array_filter([
            'blood_group' => $data['blood_group'] ?? $patient->blood_group,
            'allergies' => $data['allergies'] ?? $patient->allergies,
            'chronic_diseases' => $data['chronic_diseases'] ?? $patient->chronic_diseases,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? $patient->emergency_contact_name,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? $patient->emergency_contact_phone,
        ], fn($value) => $value !== null));
    }

    #[Override]
    public function getProfile(User $user): ?Model
    {
        return $user->profile;
    }

    /**
     * Delete the patient profile record linked to the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function delete(User $user): void
    {
        Patient::where('user_id', $user->id)->delete();
    }
}
