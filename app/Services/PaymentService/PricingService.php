<?php

declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Models\Pricing;
use Illuminate\Support\Facades\DB;

/**
 * Class PricingService
 * Governs dynamic medical procedure price cards owned by doctors.
 */
class PricingService
{
    /**
     * Store new pricing card.
     *
     * @param array<string, mixed> $data
     * @param int $doctorId
     * @return Pricing
     */
    public function createPricing(array $data, int $doctorId): Pricing
    {
        return DB::transaction(function () use ($data, $doctorId) {
            return Pricing::create([
                'doctor_id'    => $doctorId,
                'service_name' => $data['service_name'],
                'amount'       => $data['amount'],
            ]);
        });
    }

    /**
     * Update price catalog entry.
     *
     * @param Pricing $pricing
     * @param array<string, mixed> $data
     * @return Pricing
     */
    public function updatePricing(Pricing $pricing, array $data): Pricing
    {
        return DB::transaction(function () use ($pricing, $data) {
            $pricing->update([
                'service_name' => $data['service_name'],
                'amount'       => $data['amount'],
            ]);

            return $pricing->refresh();
        });
    }
}
