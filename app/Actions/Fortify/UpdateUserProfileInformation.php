<?php

namespace App\Actions\Fortify;

use App\Factories\Validation\RoleValidationFactory;
use App\Models\User;
use App\Policies\Concerns\HasClinicalProfiles;
use App\Services\UserService;
use App\Strategies\Validation\CoreUserRules;
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
        $userRules    = CoreUserRules::getUpdateRules($user->id);
        $userMessages = CoreUserRules::getUpdateMessages();

        $validated = Validator::make($input, $userRules, $userMessages)->validate();

        $oldEmail = $user->email;

        $updatedUser = $this->userService->updateCoreProfile($user, $validated);

        if ($validated['email'] !== $oldEmail && $updatedUser instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($updatedUser, $validated);
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
