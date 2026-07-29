<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'                => $user->id,
                    'first_name'        => $user->first_name,
                    'middle_name'       => $user->middle_name,
                    'last_name'         => $user->last_name,
                    'username'          => $user->username,
                    'email'             => $user->email,
                    'phone'             => $user->phone,
                    'gender'            => $user->gender,
                    'date_of_birth'     => $user->date_of_birth,
                    'address'           => $user->address,
                    'role'              => $user->role,
                    'profile_photo_url' => $user->profile_photo_url,
                ] : null,
            ],
            'roleData' => $user ? $this->transformRoleData($user) : null,
            'flash'    => [
                'success' => static fn() => $request->session()->get('success'),
                'error'   => static fn() => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * Securely maps and serializes polymorphic profile metadata to prevent database schema leakage.
     *
     * @param  \App\Models\User  $user
     * @return array<string, mixed>|null
     */
    protected function transformRoleData($user): ?array
    {
        return match ($user->role) {
            'patient' => $user->patient ? [
                'id'                     => $user->patient->id,
                'blood_group'            => $user->patient->blood_group,
                'emergency_contact_name' => $user->patient->emergency_contact_name,
                'emergency_contact_phone'=> $user->patient->emergency_contact_phone,
            ] : null,

            'doctor' => $user->doctor ? [
                'id'                => $user->doctor->id,
                'specialization_id' => $user->doctor->specialization_id,
                'license_number'    => $user->doctor->license_number,
                'experience_years'  => $user->doctor->experience_years,
                'bio'               => $user->doctor->bio,
            ] : null,

            'receptionist' => $user->receptionist ? [
                'id'              => $user->receptionist->id,
                'department_id'   => $user->receptionist->department_id,
                'employee_number' => $user->receptionist->employee_number,
            ] : null,

            default => null, 
        };
    }
}
