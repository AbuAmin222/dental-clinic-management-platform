<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileRoleController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        match ($user->role) {
            'patient' => $this->updatePatientData($request, $user),
            'doctor' => $this->updateDoctorData($request, $user),
            'receptionist' => $this->updateReceptionistData($request, $user),
            default => abort(403, 'Unauthorized action or invalid role.'),
        };

        return back()->with('flash', [
            'banner' => 'Professional profile credentials synced successfully.'
        ]);
    }

    protected function updatePatientData(Request $request, $user)
    {
        $validated = $request->validate([
            'blood_group'             => ['nullable', 'string', 'max:5'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'allergies'               => ['nullable', 'string'],
            'chronic_diseases'        => ['nullable', 'string'],
            'medical_notes'           => ['nullable', 'string'],
        ]);

        // تحديث أو إنشاء السجل المرتبط بالـ Patient فوراً
        $user->patient()->updateOrCreate(['user_id' => $user->id], $validated);
    }

    protected function updateDoctorData(Request $request, $user)
    {
        $validated = $request->validate([
            'specialization_id' => ['required', 'exists:specializations,id'],
            'license_number'    => ['required', 'string', 'max:100'],
            'experience_years'  => ['required', 'integer', 'min:0'],
            'bio'               => ['nullable', 'string'],
        ]);

        $user->doctor()->updateOrCreate(['user_id' => $user->id], $validated);
    }

    protected function updateReceptionistData(Request $request, $user)
    {
        $validated = $request->validate([
            'employee_number' => ['required', 'string', 'max:100'],
            'department_id'   => ['required', 'exists:departments,id'],
            'hiring_date'     => ['required', 'date'],
        ]);

        $user->receptionist()->updateOrCreate(['user_id' => $user->id], $validated);
    }
}
