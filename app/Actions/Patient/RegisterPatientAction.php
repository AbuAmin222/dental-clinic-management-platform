<?php

declare(strict_types=1);

namespace App\Actions\Patient;

use App\Factories\Validation\RoleValidationFactory;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Jetstream;

class RegisterPatientAction
{
    protected UserService $userService;


    /**
     * Atomically create user profile and delegate dynamic patient profile store to factory strategy.
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function execute(array $data): User
    {
        Validator::make($data, [
            'role' => 'nullable',
            'string',
            'in:patient',
        ], [
            'role.in' => 'The role of the selected user is not supported in the system(Should be patient.).',
        ])->validate();

        $password = $data['identity_number'];

        $roleStrategy = RoleValidationFactory::make('patient');
        $ruleStrategy = $roleStrategy->getRegistrationRules();
        $rulesMessage = $roleStrategy->messages();

        $rules = array_merge($ruleStrategy, [
            'password' => $password,
            'terms'    => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : [],
        ]);

        $message = array_merge($rulesMessage, [
            'terms.required'    => 'Should Accepted Terms and Policies',
        ]);

        $fullData = array_merge($data, [
            'password' => $password,
        ]);

        $validatedData = Validator::make($fullData, $rules, $message)->validate();

        return $this->userService->registerUser($validatedData);
    }
}
