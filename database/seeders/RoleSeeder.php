<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Must run before ANY user is created (DatabaseSeeder calls this first) — User::assignRole()
 * looks up Role::where('name', ...)->firstOrFail() and will hard-fail otherwise.
 */
class RoleSeeder extends Seeder
{
    private const PROFILE_TYPES = [
        'admin'        => 'admin',
        'doctor'       => 'doctor',
        'receptionist' => 'receptionist',
        'patient'      => 'patient',
        'financial'    => 'financial',
    ];

    private const DESCRIPTIONS = [
        'admin'        => 'Full hierarchical permissions over the system.',
        'doctor'       => 'Attending physician responsible for medical records and appointments.',
        'receptionist' => 'Responsible for appointments, patient registration, and forwarding invoice requests.',
        'patient'      => 'Beneficiary of services, appointments, and invoices.',
        'financial'    => 'Responsible for issuing and approving invoices and processing payments.',
    ];

    public function run(): void
    {
        foreach (UserRole::cases() as $role) {
            Role::updateOrCreate(
                ['name' => $role->value],
                [
                    'display_name' => $role->label(),
                    'description'  => self::DESCRIPTIONS[$role->value],
                    'profile_type' => self::PROFILE_TYPES[$role->value],
                    'is_system'    => true,
                ]
            );
        }
    }
}
