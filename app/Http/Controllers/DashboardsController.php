<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardsController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        return match ($role) {
            'doctor'       => redirect()->route('doctor.dashboard'),
            'patient'      => redirect()->route('patient.dashboard'),
            'receptionist' => redirect()->route('receptionist.dashboard'),
            'admin'        => redirect()->route('admin.dashboard'),
            default        => Inertia::render('Dashboard'),
        };
    }
}
