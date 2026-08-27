<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // --- Foundational / lookup data (no FK dependencies on user-generated data) ---
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            DepartmentSeeder::class,
            SpecializationSeeder::class,

            // Admin registery phase
            AdminSeeder::class,

            // --- Phase 3: exactly one fully hydrated, predictable user per role ---
            DemoUsersSeeder::class,

            // --- Role-bound profile entities (each owns a `users` row via user_id) ---
            DoctorSeeder::class,
            PatientSeeder::class,
            ReceptionistSeeder::class,
            FinancialSeeder::class,

            // --- Doctor-dependent operational data ---
            PricingSeeder::class,
            DoctorScheduleSeeder::class,

            // --- Clinical scheduling & records (depend on Doctor + Patient) ---
            TreatmentCourseSeeder::class,
            AppointmentSeeder::class,
            DentalRecordSeeder::class,
            DentalChartSeeder::class,

            // --- Billing (depends on Appointment + Pricing) ---
            InvoiceSeeder::class,

            // --- Payments (depend on Financial + Invoice) ---
            LocalPaymentMethodSeeder::class,
            PaymentTransactionSeeder::class,

            // --- Payroll (depends on staff Users + Financial) ---
            SalaryPaymentSeeder::class,

        ]);



        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // $user = User::create([
        //     'first_name' => 'reception',
        //     'middle_name' => 'test',
        //     'last_name' => '001',
        //     'username' => 'reception',
        //     'email' => 'reception@clinic.com',
        //     'identity_number' => '123456789',
        //     'phone' => '0567878782',
        //     'password' => Hash::make('Reception@123'),
        //     'role' => 'receptionist',
        //     'gender' => 'Male',
        //     'date_of_birth' => '1999-01-02',
        //     'address' => 'Gaza Gaza',
        //     'identity_photo_path' => 'uploads\local',
        //     'profile_photo_path' => 'uploads\private',
        // ]);

        // Receptionist::create([
        //     'user_id' => $user->id,
        //     'department_id' => '1',
        //     'employee_number' => 'EM-675765',
        //     'hiring_date' => Date::now(),
        // ]);
    }
}
