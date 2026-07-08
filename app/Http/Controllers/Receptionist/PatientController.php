<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    // دالة استعراض المرضى مع البحث والتقسيم الاحترافي
    public function index(Request $request)
    {
        $search = $request->input('search');

        $patients = Patient::with('user')
            ->whereHas('user', function ($query) use ($search) {
                if ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('identity_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                }
            })
            ->latest() // عرض الأحدث أولاً
            ->paginate(10)
            ->withQueryString(); // الحفاظ على قيمة البحث عند التنقل بين الصفحات

        return inertia('Receptionist/Patients/Index', [
            'patients' => $patients,
            'filters' => $request->only(['search']) // إرجاع قيمة البحث للـ Vue للحفاظ عليها داخل حقل الإدخال
        ]);
    }

    public function create()
    {
        return inertia('Receptionist/Create');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        Validator::make($input, [
            'first_name' => 'required|string|max:50',
            'middle_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',

            'identity_number' => 'required|numeric|unique:users,identity_number',
            'phone' => ['required', 'string', 'regex:/^(059|056)\d{7}$/'],

            'username' => ['required', 'string', 'min:3', 'max:25', 'unique:users'],
            'email' => 'required|email|unique:users,email',

            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'address' => ['required', 'string', 'min:5', 'max:255'],

            'blood_group' => ['required', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'emergency_contact_name' => 'required|string|max:50',
            'emergency_contact_phone' => ['required', 'string', 'regex:/^(059|056)\d{7}$/'],
        ], [
            'first_name.required' => 'First Name is required',
            'middle_name.required' => 'Middle Name is required',
            'last_name.required' => 'last Name is required',

            'identity_number.required' => 'Identity Number is required',
            'phone.required' => 'Phone is required',

            'username.required' => 'Username is required',
            'email.required' => 'Email is required',

            'date_of_birth.required' => 'date of Birth is required',
            'gender.required' => 'Gender is required',
            'address.required' => 'Address is required',

            'blood_group.required' => 'blood_group is required',
            'emergency_contact_name.required' => 'Emergency Contact Name is required',
            'emergency_contact_phone.required' => 'Emergency Contact Phone is required',
        ])->validate();


        DB::transaction(function () use ($input) {
            $user = User::create([
                'first_name' =>  $input['first_name'],
                'middle_name' =>  $input['middle_name'],
                'last_name' =>  $input['last_name'],

                'username' => $input['username'],
                'email' =>  $input['email'],

                'identity_number' =>  $input['identity_number'],
                'phone' =>  $input['phone'],

                'password' => Hash::make($input['identity_number']),

                'role' =>  $input['role'],
                'gender' =>  $input['gender'],
                'date_of_birth' =>  $input['date_of_birth'],
                'address' =>  $input['address'],
            ]);
            $this->createRoleProfile($user, $input);
        });
        return redirect()->route('receptionist.patients.index')
            ->with('success', 'Patient registered successfully!');
    }
    protected function createRoleProfile(User $user, array $input)
    {
        switch ($input['role']) {
            case 'patient':
                Patient::create([
                    'user_id' => $user->id,
                    'blood_group' => $input['blood_group'],
                    'allergies' => $input['allergies'] ?? null,
                    'chronic_diseases' => $input['chronic_diseases'] ?? null,
                    'emergency_contact_name' => $input['emergency_contact_name'],
                    'emergency_contact_phone' => $input['emergency_contact_phone'],
                ]);
                break;
        }
    }

    public function show(Patient $patient)
    {
        // جلب المريض مع بيانات المستخدم، ومواعيده مرتبة تنازلياً مع الطبيب والفاتورة
        $patient->load([
            'user',
            'appointments' => function ($query) {
                $query->with(['doctor.user', 'invoices'])
                    ->orderBy('appointment_date', 'desc')
                    ->orderBy('start_time', 'desc');
            }
        ]);

        return inertia('Receptionist/Patients/Show', [
            'patient' => $patient
        ]);
    }

    // ⚡ تم تحويلها إلى public وتجهيزها لاستقبال طلبات الـ HTTP والـ Axios
    public function checkUsername(Request $request)
    {
        // 1. التحقق من المدخلات بشكل صارم قبل فحص قاعدة البيانات
        $request->validate([
            'username' => 'required|string|min:3|max:25'
        ]);

        // 2. فحص ما إذا كان اسم المستخدم محجوزاً مسبقاً في جدول الـ users
        $exists = User::where('username', $request->username)->exists();

        // 3. إرجاع استجابة JSON مهيأة تماماً للـ Frontend الخاص بك
        return response()->json([
            'valid' => !$exists, // يكون متاحاً (true) إذا لم يكن موجوداً مسبقاً
            'message' => $exists
                ? '✕ This username is already taken.'
                : '✓ Username is available!'
        ]);
    }
}
