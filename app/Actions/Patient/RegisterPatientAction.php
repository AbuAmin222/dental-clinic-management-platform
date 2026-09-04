<?php

declare(strict_types=1);

namespace App\Actions\Patient;

use App\Factories\Validation\RoleValidationFactory;
use App\Models\User;
use App\Services\Security\AccountVerificationService;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Jetstream;
use App\Enums\UserRole;

class RegisterPatientAction
{
    protected UserService $userService;
    protected AccountVerificationService $accountVerificationService;

    /**
     * Constructor Dependency Injection restored.
     */
    public function __construct(UserService $userService, AccountVerificationService $accountVerificationService)
    {
        $this->userService = $userService;
        $this->accountVerificationService = $accountVerificationService;
    }


    /**
     * Atomically create user profile and delegate dynamic patient profile store to factory strategy.
     *
     * Business Rule (confirmed): the initial account password for a receptionist-initiated
     * patient registration is the patient's identity_number, to be changed by the patient later.
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function execute(array $data): User
    {
        $role = UserRole::Patient->value;
        Validator::make($data, [
            'role' => ['nullable', 'string', 'in:' . $role],
        ], [
            'role.in' => 'The role of the selected user is not supported in the system (should be patient).',
        ])->validate();

        $password = $data['identity_number'];

        $roleStrategy = RoleValidationFactory::make('patient');
        $rules = $roleStrategy->getRegistrationRules();
        $messages = $roleStrategy->messages();

        unset($rules['password']);
        $rules = array_merge($rules, [
            'password' => ['required', 'string'],
            'terms'    => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : [],
        ]);

        $messages = array_merge($messages, [
            'terms.required' => 'Should Accepted Terms and Policies',
        ]);

        $fullData = array_merge($data, [
            'password' => $password,
            'terms'    => 1,
        ]);

        $validatedData = Validator::make($fullData, $rules, $messages)->validated();
        $data = array_merge($validatedData, ['staff' => $fullData['staff']]);

        $user = $this->userService->registerUser($data);

        // $user->forceFill(['must_change_password' => true])->save();
        $this->accountVerificationService->generateAndSendVerificationCode($user);

        return $user;
    }
}
