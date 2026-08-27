<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Financial;
use App\Models\LocalPaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LocalPaymentMethod>
 */
class LocalPaymentMethodFactory extends Factory
{
    protected $model = LocalPaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['bank_transfer', 'visa_card', 'mobile_wallet']);

        return [
            'financial_id' => Financial::factory(),
            'title' => match ($type) {
                'bank_transfer' => 'Bank of Palestine — Local Transfer',
                'visa_card' => 'Clinic Visa Card',
                'mobile_wallet' => 'JawwalPay Wallet',
            },
            'bank_phone_number' => $type === 'mobile_wallet' ? $this->faker->numerify('059#######') : null,
            'visa_card_number' => $type === 'visa_card' ? $this->faker->creditCardNumber() : null,
            'account_number' => $type === 'bank_transfer' ? $this->faker->numerify('##########') : null,
            'iban' => $type === 'bank_transfer' ? 'PS' . $this->faker->numerify('##################') : null,
            'qr_code_path' => null,
            'is_visible_to_patient' => true,
            'is_active' => true,
        ];
    }

    /**
     * A method hidden from the patient-facing payment screen (internal/manual use only).
     */
    public function internalOnly(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_visible_to_patient' => false,
        ]);
    }
}
