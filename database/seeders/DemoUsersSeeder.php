<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Financial;
use App\Models\LocalPaymentMethod;
use App\Models\Patient;
use App\Models\Pricing;
use App\Models\Receptionist;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Provisions exactly ONE predictable, fully hydrated user account per system role, as
 * required for QA/demo/local-dev login. All writes go through updateOrCreate() keyed on
 * `email`, so re-running `php artisan db:seed` is a no-op after the first run — it will
 * never create duplicate accounts or violate the unique constraints on
 * users.username/identity_number or the profile tables' unique keys.
 *
 * Credentials (all use the same demo password, hashed via User's 'hashed' cast):
 *   admin@dental.test         / password123
 *   doctor@dental.test        / password123
 *   receptionist@dental.test  / password123
 *   patient@dental.test       / password123
 *   financial@dental.test     / password123
 *
 * Must run AFTER: RoleSeeder, DepartmentSeeder, SpecializationSeeder.
 */
class DemoUsersSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'password123';

    public function run(): void
    {
        $this->seedAdmin();
        $this->seedDoctor();
        $this->seedReceptionist();
        $this->seedPatient();
        $this->seedFinancial();

        $this->command?->info('✅ One demo user per role provisioned (admin/doctor/receptionist/patient/financial @dental.test, password: ' . self::DEMO_PASSWORD . ').');
    }

    private function baseUserAttributes(): array
    {
        return [
            'password' => Hash::make(self::DEMO_PASSWORD),
            'is_active' => true,
            'email_verified_at' => now(),
            'identity_photo_path' => null,
            'profile_photo_path' => null,
        ];
    }

    private function seedAdmin(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@dental.test'],
            [
                ...$this->baseUserAttributes(),
                'first_name' => 'Sami',
                'middle_name' => 'Khalil',
                'last_name' => 'Nasser',
                'username' => 'admin',
                'identity_number' => '100000001',
                'phone' => '0591000001',
                'gender' => 'Male',
                'date_of_birth' => '1985-03-14',
                'address' => 'Al-Rimal, Gaza',
                'base_salary' => 15000.00,
            ]
        );

        $user->assignRole(UserRole::Admin->value, true);
    }

    private function seedDoctor(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'doctor@dental.test'],
            [
                ...$this->baseUserAttributes(),
                'first_name' => 'Layla',
                'middle_name' => 'Ahmad',
                'last_name' => 'Haddad',
                'username' => 'dr.haddad',
                'identity_number' => '100000002',
                'phone' => '0591000002',
                'gender' => 'Female',
                'date_of_birth' => '1988-07-22',
                'address' => 'Al-Nasr, Gaza',
                'base_salary' => 20000.00,
            ]
        );

        $user->assignRole(UserRole::Doctor->value, true);

        $specialization = Specialization::firstOrCreate(
            ['slug' => 'general-dentistry'],
            ['name' => 'General Dentistry', 'description' => 'Expert services in General Dentistry.', 'is_active' => true]
        );

        $doctor = Doctor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'specialization_id' => $specialization->id,
                'license_number' => 'DOC-DEMO-001',
                'bio' => 'General dentist with a decade of clinical experience in preventive and restorative dentistry.',
                'experience_years' => 10,
            ]
        );

        // Standard Sunday–Thursday working week.
        foreach (['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'] as $day) {
            DoctorSchedule::updateOrCreate(
                ['doctor_id' => $doctor->id, 'day_of_week' => $day, 'start_time' => '09:00'],
                ['end_time' => '17:00', 'is_active' => true]
            );
        }

        // Baseline service pricing so the demo doctor can actually bill for visits.
        foreach (
            [
                'Consultation' => 100.00,
                'Cleaning' => 150.00,
                'Filling' => 250.00,
                'X-Ray' => 80.00,
            ] as $service => $amount
        ) {
            Pricing::firstOrCreate(
                ['doctor_id' => $doctor->id, 'service_name' => $service],
                ['amount' => $amount]
            );
        }
    }

    private function seedReceptionist(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'receptionist@dental.test'],
            [
                ...$this->baseUserAttributes(),
                'first_name' => 'Mona',
                'middle_name' => 'Yousef',
                'last_name' => 'Odeh',
                'username' => 'reception.odeh',
                'identity_number' => '100000003',
                'phone' => '0591000003',
                'gender' => 'Female',
                'date_of_birth' => '1996-11-02',
                'address' => 'Tal al-Hawa, Gaza',
                'base_salary' => 6000.00,
            ]
        );

        $user->assignRole(UserRole::Receptionist->value, true);

        $department = Department::firstOrCreate(
            ['slug' => 'front-desk'],
            ['name' => 'Front Desk', 'description' => 'Department responsible for Front Desk', 'is_active' => true]
        );

        Receptionist::updateOrCreate(
            ['user_id' => $user->id],
            [
                'department_id' => $department->id,
                'employee_number' => 'EMP-DEMO-001',
                'hiring_date' => '2022-01-10',
            ]
        );
    }

    private function seedPatient(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'patient@dental.test'],
            [
                ...$this->baseUserAttributes(),
                'first_name' => 'Karim',
                'middle_name' => 'Fadi',
                'last_name' => 'Shawa',
                'username' => 'patient.shawa',
                'identity_number' => '100000004',
                'phone' => '0591000004',
                'gender' => 'Male',
                'date_of_birth' => '1994-05-30',
                'address' => 'Al-Zaytoun, Gaza',
                'base_salary' => null,
            ]
        );

        $user->assignRole(UserRole::Patient->value, true);

        Patient::updateOrCreate(
            ['user_id' => $user->id],
            [
                'blood_group' => 'O+',
                'allergies' => 'Penicillin',
                'chronic_diseases' => null,
                'emergency_contact_name' => 'Rania Shawa',
                'emergency_contact_phone' => '0592000004',
                'medical_notes' => 'No prior dental surgeries. Mild dental anxiety noted at intake.',
            ]
        );
    }

    private function seedFinancial(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'financial@dental.test'],
            [
                ...$this->baseUserAttributes(),
                'first_name' => 'Nadia',
                'middle_name' => 'Samir',
                'last_name' => 'Barghouti',
                'username' => 'finance.barghouti',
                'identity_number' => '100000005',
                'phone' => '0591000005',
                'gender' => 'Female',
                'date_of_birth' => '1990-09-18',
                'address' => 'Al-Rimal, Gaza',
                'base_salary' => 8000.00,
            ]
        );

        $user->assignRole(UserRole::Financial->value, true);

        $financial = Financial::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_number' => 'FIN-DEMO-001',
                'hiring_date' => '2021-06-01',
                'years_experience' => 5,
                'specialization' => 'Revenue Cycle Management',
                'metadata' => ['department' => 'Finance & Billing'],
                'is_profile_completed' => true,
            ]
        );

        LocalPaymentMethod::updateOrCreate(
            ['financial_id' => $financial->id, 'title' => 'Bank of Palestine — Local Transfer'],
            [
                'account_number' => '1234567890',
                'iban' => 'PS92PALS000000000000123456789',
                'bank_phone_number' => null,
                'visa_card_number' => null,
                'qr_code_path' => null,
                'is_visible_to_patient' => true,
                'is_active' => true,
            ]
        );
    }
}
