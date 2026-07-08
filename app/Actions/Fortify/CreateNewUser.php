<?php

namespace App\Actions\Fortify;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Receptionist;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Http\UploadedFile;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            // Step 1
            'role' => ['required', 'in:patient,doctor,receptionist'],
            // end

            // Step 2
            'first_name' => ['required', 'string', 'min:3', 'max:20'],
            'middle_name' => ['required', 'string', 'min:3', 'max:20'],
            'last_name' => ['required', 'string', 'min:3', 'max:20'],
            'identity_number' => ['required', 'string', 'size:9', 'unique:users'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:Male,Female'],
            // end

            // Step 3
            'username' => ['required', 'string', 'min:3', 'max:25', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'phone' => ['required', 'regex:/^(059|056)\d{7}$/'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            // end

            // Step 4
            'identity_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
            // end

            // Step 5 - Patient Data
            'blood_group' => [Rule::requiredIf($input['role'] === 'patient'), 'nullable', 'string'],
            // 'allergies' => [Rule::requiredIf($input['role'] === 'patient'), 'nullable', 'string'],
            // 'chronic_diseases' => [Rule::requiredIf($input['role'] === 'patient'), 'nullable', 'string'],
            'emergency_contact_name' => [Rule::requiredIf($input['role'] === 'patient'), 'nullable', 'string'],
            'emergency_contact_phone' => [Rule::requiredIf($input['role'] === 'patient'), 'nullable', 'regex:/^(059|056)\d{7}$/'],
            // end

            // Step 6 - Doctor Data
            'specialization_id' => [Rule::requiredIf($input['role'] === 'doctor'), 'nullable', 'exists:specializations,id'],
            'license_number' => [Rule::requiredIf($input['role'] === 'doctor'), 'nullable'],
            'experience_years' => [Rule::requiredIf($input['role'] === 'doctor'), 'nullable'],
            // 'bio' => [Rule::requiredIf($input['role'] === 'doctor'), 'nullable', 'string'],
            // end

            // Step 7 - Receptionist Data
            'department_id' => [Rule::requiredIf($input['role'] === 'receptionist'), 'nullable', 'exists:departments,id'],
            'employee_number' => [Rule::requiredIf($input['role'] === 'receptionist'), 'nullable'],
            'hiring_date' => [Rule::requiredIf($input['role'] === 'receptionist'), 'nullable'],
            // end

            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'role.in' => 'Please select a valid account type.',

            'first_name.required' => 'Your first name is required.',

            'username.required' => 'Username is required',
            'email.unique' => 'This email address is already in use.',

            'identity_number.unique' => 'This identity number is already registered.',
            'identity_number.size' => 'Identity number must be exactly 9 digits.',

            'identity_photo.required' => 'Please upload your identity card photo.',

            'specialization_id.required' => 'Please select your medical specialization.',
            'blood_group.required' => 'Blood group is mandatory for patients.',
        ])->validate();

        return DB::transaction(function () use ($input) {
            $name = $input['first_name'] . ' ' . $input['last_name'];

            $roleDir = strtolower($input['role']);

            $profilePath = null;
            $identityPath = null;

            if (isset($input['profile_photo']) && $input['profile_photo'] instanceof UploadedFile) {
                $profilePath = storage_engine()->upload(
                    $name,
                    $input['profile_photo'],
                    "uploads/{$roleDir}/profiles"
                );
            }

            if (isset($input['identity_photo']) && $input['identity_photo'] instanceof UploadedFile) {
                $identityPath = storage_engine()->upload(
                    $name,
                    $input['identity_photo'],
                    "secure/{$roleDir}/identities",
                    'local'
                );
            }

            $user = User::create([
                'first_name' => $input['first_name'],
                'middle_name' => $input['middle_name'],
                'last_name' => $input['last_name'],

                'username' => $input['username'],
                'email' => $input['email'],

                'identity_number' => $input['identity_number'],
                'phone' => $input['phone'],

                'password' => Hash::make($input['password']),

                'role' => $input['role'],
                'gender' => $input['gender'],
                'date_of_birth' => $input['date_of_birth'],
                'address' => $input['address'],

                'identity_photo_path' => $identityPath,
                'profile_photo_path' => $profilePath,
            ]);

            $this->createRoleProfile($user, $input);

            session()->flash('success', 'Welcome to our Clinic! Your account is ready.');

            return $user;
        });
    }

    protected function createRoleProfile(User $user, array $input): void
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
            case 'doctor':
                $specialization = Specialization::query()->findOrFail($input['specialization_id'], 'id');
                Doctor::create([
                    'user_id' => $user->id,
                    'specialization_id' => $specialization->id,
                    'license_number' => $input['license_number'],
                    'bio' => $input['bio'] ?? null,
                    'experience_years' => $input['experience_years'] ?? 0,
                ]);
                break;

            case 'receptionist':
                $department = Department::query()->findOrFail($input['department_id'], 'id');
                Receptionist::create([
                    'user_id' => $user->id,
                    'department_id' => $department->id,
                    'employee_number' => $input['employee_number'],
                    'hiring_date' => $input['hiring_date'],
                ]);
                break;
        }
    }
}
