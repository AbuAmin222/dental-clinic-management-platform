<?php

namespace App\Strategies\Profile;

use App\Contracts\Profile\RoleProfileStrategyInterface;
use App\Models\User;
use App\Models\Receptionist;
use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Override;

class ReceptionistProfileStrategy implements RoleProfileStrategyInterface
{
    /**
     * Create a new receptionist profile record mapped to a specific department.
     *
     * @param  \App\Models\User  $user
     * @param  array<string, mixed>  $data
     * @return void
     */
    public function create(User $user, array $data): void
    {
        $department = Department::findOrFail($data['department_id'], ['id']);

        Receptionist::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'employee_number' => $data['employee_number'],
            'hiring_date' => $data['hiring_date'],
        ]);
    }

    /**
     * Update the receptionist profile record associated with the user.
     *
     * @param  \App\Models\User  $user
     * @param  array<string, mixed>  $data
     * @return void
     */
    public function update(User $user, array $data): void
    {
        $receptionist = Receptionist::where('user_id', $user->id)->firstOrFail();

        $receptionist->update(array_filter([
            'department_id' => $data['department_id'] ?? $receptionist->department_id,
            'employee_number' => $data['employee_number'] ?? $receptionist->employee_number,
            'hiring_date' => $data['hiring_date'] ?? $receptionist->hiring_date,
        ], fn($value) => $value !== null));
    }

    #[Override]
    public function getProfile(User $user): ?Model
    {
        return $user->profile;
    }
    /**
     * Delete the receptionist profile record linked to the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function delete(User $user): void
    {
        Receptionist::where('user_id', $user->id)->delete();
    }
}
