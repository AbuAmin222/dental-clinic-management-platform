<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Models\Invoice;

class PatientController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $patientData = Patient::where('user_id', $user->id)->with([
            'appointments' => function ($query) {
                $query->with(['doctor.user', 'invoices'])->latest();
            },
            'dentalRecords' => function ($query) {
                $query->with(['doctor.user', 'appointment'])->latest();
            },
            'invoices' => function ($query) {
                $query->with(['doctor.user', 'appointment'])->latest();
            }
        ])->first();

        $stats = [
            'total_appointments'   => $patientData ? $patientData->appointments->count() : 0,
            'pending_appointments' => $patientData ? $patientData->appointments->where('status', 'pending')->count() : 0,
            'total_treatments'     => $patientData ? $patientData->dentalRecords->count() : 0,
            'remaining_balance'    => $patientData ? $patientData->invoices->whereIn('status', ['unpaid', 'partially_paid'])->sum('balance_amount') : 0,
        ];

        $invoices = $patientData ? $patientData->invoices : [];

        return Inertia::render('Patient/Dashboard', [
            'patient'  => $patientData,
            'stats'    => $stats,
            'invoices' => $invoices,
        ]);
    }

    public function createAppointment()
    {
        $doctors = Doctor::with(['user', 'specialization'])->get()->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'name' => 'Dr. ' . $doctor->user->first_name . ' ' . $doctor->user->last_name,
                'specialization' => $doctor->specialization ? $doctor->specialization->name : 'General Dentistry'
            ];
        });

        return Inertia::render('Patient/AppointmentCreate', [
            'doctors' => $doctors
        ]);
    }

    public function storeAppointment(Request $request)
    {
        $userId = Auth::id();
        $patient = Patient::where('user_id', $userId)->firstOrFail();

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'reason_for_visit' => 'required|string|min:10|max:1000',
        ]);

        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = $startTime->copy()->addMinutes(30)->format('H:i');

        //  (Overlap Collision Prevention)
        $isOverlapping = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($request, $endTime) {
                $query->where(function ($q) use ($request, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $request->start_time);
                });
            })->exists();

        if ($isOverlapping) {
            return back()->withErrors([
                'start_time' => 'The selected time slot conflicts with an existing appointment for this doctor. Please choose a different time or date.'
            ]);
        }

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'start_time' => $request->start_time,
            'end_time' => $endTime,
            'status' => 'pending',
            'reason_for_visit' => $request->reason_for_visit,
        ]);

        return redirect()->route('patient.dashboard')->with('success', 'Your appointment has been requested successfully.');
    }

    // 1. Show payment page for invoice
    public function checkoutInvoice(Invoice $invoice)
    {
        $patient = Auth::user()->patient;
        if ($invoice->patient_id !== $patient->id) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Patient/InvoicePayment', [
            'invoice' => $invoice->load('doctor.user')
        ]);
    }
}
