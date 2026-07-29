<?php

namespace App\Actions\Fortify;

use App\Factories\Validation\RoleValidationFactory;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Verify and pass data to UserService for secure, isolated registration in a single transaction..
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'role' => ['required', 'string', 'in:patient,doctor,receptionist'],
        ], [
            'role.required' => 'The users role must be defined to complete the registration process.',
            'role.in'       => 'The role of the selected user is not supported in the system.',
        ])->validate();

        $roleName = $input['role'];

        $roleStrategy = RoleValidationFactory::make($roleName);

        $rules    = $roleStrategy->getRegistrationRules();

        $ruleMessages = $roleStrategy->messages();

        $finalRules = array_merge($rules, [
            'password' => $this->passwordRules(),
            'terms'    => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : [],
        ]);

        $finalMessages = array_merge($ruleMessages, [
            'password.required' => 'A password is required to secure your personal account.',
            'terms.accepted'    => 'The terms and conditions and privacy policy must be accepted to continue.',
        ]);

        $validatedData = Validator::make($input, $finalRules, $finalMessages)->validate();

        return $this->userService->registerUser($validatedData);
    }
}
