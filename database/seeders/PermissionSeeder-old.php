<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // --- appointments (AppointmentPolicy) ---
            ['appointments.viewAny', 'SHOW ALL APPOINTEMTS', 'appointments'],
            ['appointments.view', 'SHOW APPOINTMENT DETAILED', 'appointments'],
            ['appointments.create', 'CREATE NEW APPOINTMENT', 'appointments'],
            ['appointments.update', 'EDIT APPOINTMENT', 'appointments'],
            ['appointments.delete', 'CANCCELED|DELETED APPOINTMENT', 'appointments'],

            // --- dental_records (DentalRecordPolicy) ---
            ['dental_records.viewAny', 'SHOW ALL DENTAL RECORDS', 'dental_records'],
            ['dental_records.view', 'SHOW MEDICAL HISTORY', 'dental_records'],
            ['dental_records.create', 'CREATE MIDECAL HISTORY', 'dental_records'],
            ['dental_records.update', 'EDIT MEDICAL HISTORY', 'dental_records'],
            ['dental_records.delete', 'DELETE MEDICAL HISTORY', 'dental_records'],
            ['dental_records.restore', 'RECOVERY DELETED MEDICAL HISTORY', 'dental_records'],
            ['dental_records.forceDelete', 'PERMANENTLY DELETE MEDICAL RECORD', 'dental_records'],

            // --- invoices (InvoicePolicy) ---
            ['invoices.viewAny', 'SHOW ALL INVOICES', 'invoices'],
            ['invoices.view', 'SHOW INVOICE', 'invoices'],
            ['invoices.create', 'REQUEST A NEW INVOICE (RECEIPT)', 'invoices'],
            ['invoices.update', 'EDIT INVOICE', 'invoices'],
            ['invoices.delete', 'DELETE INVOICE', 'invoices'],
            ['invoices.restore', 'RECVERY DELETED INVOICE', 'invoices'],
            ['invoices.forceDelete', 'PERMANENTLY DELETED INVOICE', 'invoices'],
            ['invoices.pay', 'PAY INVOICE', 'invoices'],
            ['invoices.issue', 'INVOICE APPROVAL|ISSUANCE (FINANCE)', 'invoices'],

            // --- pricings (PricingPolicy) ---
            ['pricings.viewAny', 'VIEW PRICINGS LIST', 'pricings'],
            ['pricings.view', 'VIEW SERVICE PRICING', 'pricings'],
            ['pricings.create', 'ADD SERVICE PRICING', 'pricings'],
            ['pricings.update', 'UPDATE SERVICE PRICING', 'pricings'],
            ['pricings.delete', 'DELETE SERVICE PRICING', 'pricings'],
            ['pricings.restore', 'RESTORE DELETED SERVICE PRICING', 'pricings'],
            ['pricings.forceDelete', 'PERMANENTLY DELETE SERVICE PRICING', 'pricings'],

            // --- patients (PatientPolicy) ---
            ['patients.viewAny', 'VIEW ALL PATIENTS', 'patients'],
            ['patients.view', 'VIEW PATIENT PROFILE', 'patients'],
            ['patients.create', 'REGISTER NEW PATIENT', 'patients'],
            ['patients.update', 'UPDATE PATIENT DETAILS', 'patients'],
            ['patients.delete', 'DELETE PATIENT', 'patients'],

            // --- users (UserPolicy) — ACCOUNT MANAGEMENT ---
            ['users.viewAny', 'VIEW ALL USERS', 'users'],
            ['users.view', 'VIEW USER PROFILE', 'users'],
            ['users.create', 'CREATE USER ACCOUNT', 'users'],
            ['users.update', 'UPDATE USER ACCOUNT', 'users'],
            ['users.delete', 'DELETE OR DISABLE USER ACCOUNT', 'users'],
            ['users.activate', 'ACTIVATE PENDING USER ACCOUNT', 'users'],
            ['users.manage_salary', 'SET EMPLOYEE BASE SALARY', 'users'],

            // --- salary_payments (SalaryPaymentService, FINANCIAL) ---
            ['salary_payments.record', 'RECORD SALARY PAYMENT', 'salary_payments'],
            ['salary_payments.approve', 'APPROVE SALARY PAYMENT', 'salary_payments'],
            ['salary_payments.hold', 'HOLD SALARY PAYMENT', 'salary_payments'],
            ['salary_payments.reject', 'REJECT SALARY PAYMENT', 'salary_payments'],
            ['salary_payments.cancel', 'CANCEL SALARY PAYMENT', 'salary_payments'],
            ['salary_payments.markPaid', 'CONFIRM ACTUAL SALARY DISBURSEMENT', 'salary_payments'],

            // --- local_payment_methods (FINANCIAL) ---
            ['local_payment_methods.manage', 'MANAGE LOCAL PAYMENT METHODS DISPLAYED TO PATIENTS', 'local_payment_methods'],

            // --- roles/permissions (ADMIN ONLY — SYSTEM MANAGEMENT) ---
            ['system.manage_roles', 'ASSIGN OR REVOKE USER ROLES', 'system'],
            ['system.manage_permissions', 'GRANT OR REVOKE PERMISSIONS (ROLE OR USER)', 'system'],
        ];

        foreach ($permissions as [$name, $displayName, $group]) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'group' => $group],
            );
        }
    }
}
