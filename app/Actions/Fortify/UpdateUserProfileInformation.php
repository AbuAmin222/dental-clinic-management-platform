<?php

namespace App\Actions\Fortify;

use App\Factories\Validation\RoleValidationFactory;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use App\Services\UserService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    use HasClinicalProfiles;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Verify modifications and authorize saving to the service layer.
     */
    public function update(User $user, array $input): void
    {
        $roleStrategy = RoleValidationFactory::make($user->role);

        $roleRules    = $roleStrategy->getUpdateRules($user, $input);


        $roleMessages = $roleStrategy->messages();


        Validator::make($input, $roleRules, $roleMessages)
            ->validateWithBag('updateProfileInformation');

        $oldEmail = $user->email;

        // 3. Updating records through the Service Layer.
        $updatedUser = $this->userService->updateUserProfile($user, $input);

        // 4. Handling email reconfirmation in case of change.
        if ($input['email'] !== $oldEmail && $updatedUser instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($updatedUser, $input);
        }
    }

    /**
     *Reset the verification status and alert the user to reactivate their email.
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
