<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    // 1. شاشة استعراض وإدارة جميع المواعيد
    public function index(Request $request)
    {
        // بناء الاستعلام الأساسي مع العلاقات
        $query = Appointment::with(['patient.user', 'doctor.user', 'invoices']);

        // 1. الفلترة حسب حالة الموعد (إذا لم تكن القيمة 'all')
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 2. البحث الذكي باسم المريض أو رقم الهوية
        if ($request->filled('search')) {
            $search = $request->search;

            // قفل الشروط داخل دالة تجميعية لحماية بقية الفلاتر
            $query->where(function ($mainQuery) use ($search) {
                // البحث في بيانات المريض
                $mainQuery->whereHas('patient.user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('identity_number', 'like', "%{$search}%");
                })
                    // أو البحث في بيانات الطبيب المعالج للموعد
                    ->orWhereHas('doctor.user', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('invoice_status') && $request->invoice_status !== 'all') {
            $query->whereHas('invoices', function ($q) use ($request) {
                $q->where('status', $request->invoice_status);
            });
        }

        // الترتيب والتقسيم لصفحات
        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(10)
            ->withQueryString();

        return inertia('Receptionist/Appointments/Index', [
            'appointments' => $appointments,

            // إرسال قيم الفلاتر الحالية لربطها بحقول الإدخال في الـ Vue
            'filters' => $request->only(['search', 'status', 'invoice_status'])
        ]);
    }

    // 2. تحديث حالة الموعد سرياً (Confirmed, Cancelled, etc.)
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,scheduled,confirmed,completed,cancelled,no_show'
        ]);

        $appointment->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Appointment status updated successfully!');
    }

    public function create(Request $request)
    {
        $doctors = Doctor::with('user', 'specialization')->get()->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'name' => "Dr. " . $doctor->user->first_name . " " . $doctor->user->last_name,
                'spec' => "( " . $doctor->specialization->name . " )",
            ];
        });

        return inertia('Receptionist/Appointments/Create', [
            'doctors' => $doctors,
            'selected_patient_id' => $request->query('patient_id') ? (int) $request->query('patient_id') : null,
            'patients' => Patient::with('user')->get()->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->user->first_name . " " . $patient->user->last_name . " (" . $patient->user->identity_number . ")",
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        Validator::make($input, [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:now',
            'reason_for_visit' => 'nullable|string|max:500',
        ])->validate();

        $appointmentDateTime = Carbon::parse($input['appointment_date']);
        $date = $appointmentDateTime->toDateString();
        $startTime = $appointmentDateTime->toTimeString();
        $endTime = $appointmentDateTime->copy()->addMinutes(30)->toTimeString();

        // Same overlap-collision check the patient-facing booking flow already
        // has (PatientController::storeAppointment()) - nothing here was
        // stopping a receptionist from double-booking a doctor.
        $isOverlapping = Appointment::where('doctor_id', $input['doctor_id'])
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($isOverlapping) {
            return back()
                ->withErrors([
                    'appointment_date' => 'This doctor already has an appointment that overlaps with the selected time. Please choose a different time.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($input, $date, $startTime, $endTime) {
            Appointment::create([
                'patient_id'       => $input['patient_id'],
                'doctor_id'        => $input['doctor_id'],
                'appointment_date' => $date, // يستخرج التاريخ فقط: YYYY-MM-DD
                'start_time'       => $startTime, // يستخرج وقت البدء: HH:MM:SS
                'end_time'         => $endTime, // وقت النهاية الافتراضي (جلسة نصف ساعة)
                'status'           => 'confirmed',
                'reason_for_visit' => $input['reason_for_visit'] ?? null,
                'doctor_notes'     => null,
            ]);
        });

        return redirect()->route('receptionist.appointments.index')
            ->with('success', 'Appointment booked and confirmed successfully!');
    }
}
