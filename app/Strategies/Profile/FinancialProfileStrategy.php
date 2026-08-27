<?php

declare(strict_types=1);

namespace App\Strategies\Profile;

use App\Contracts\Profile\RoleProfileStrategyInterface;
use App\Models\Financial;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Mirrors ReceptionistProfileStrategy's structure exactly — same layer, same contract,
 * same conventions. `is_profile_completed` is intentionally NOT settable here directly;
 * it is only ever flipped to true by FinancialOnboardingController once every required
 * field is present, per the self-onboarding flow in the architecture document §2.b.
 */
class FinancialProfileStrategy implements RoleProfileStrategyInterface
{
    public function create(User $user, array $data): void
    {
        Financial::create([
            'user_id'          => $user->id,
            'employee_number'  => $data['employee_number'],
            'hiring_date'      => $data['hiring_date'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
            'specialization'   => $data['specialization'] ?? null,
        ]);
    }

    public function update(User $user, array $data): void
    {
        $financial = Financial::where('user_id', $user->id)->firstOrFail();

        $financial->update(array_filter([
            'employee_number'  => $data['employee_number'] ?? $financial->employee_number,
            'hiring_date'      => $data['hiring_date'] ?? $financial->hiring_date,
            'years_experience' => $data['years_experience'] ?? $financial->years_experience,
            'specialization'   => $data['specialization'] ?? $financial->specialization,
        ], fn($value) => $value !== null));
    }

    #[Override]
    public function getProfile(User $user): ?Model
    {
        return $user->profile;
    }

    public function delete(User $user): void
    {
        Financial::where('user_id', $user->id)->delete();
    }
}
