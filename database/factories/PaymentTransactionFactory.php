<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Models\Invoice;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $method = $this->faker->randomElement(PaymentMethod::cases());
        $isLocal = $method === PaymentMethod::LocalTransfer;

        return [
            'invoice_id' => Invoice::factory(),
            'transaction_id' => $isLocal ? null : $this->faker->unique()->uuid(),
            'transaction_reference' => $isLocal ? 'LOCAL-' . $this->faker->unique()->numberBetween(10000, 99999) : $this->faker->unique()->bothify('REF-########'),
            'local_payment_method_id' => null,
            'payment_method' => $method,
            'amount' => $this->faker->randomFloat(2, 50, 1500),
            'currency' => 'ILS',
            'status' => $isLocal ? PaymentTransactionStatus::HeldForReview : PaymentTransactionStatus::Completed,
            'gateway_response' => $isLocal ? null : [
                'gateway' => $method->value,
                'reference' => $this->faker->uuid(),
                'result' => 'approved',
            ],
            'proof_image_path' => $isLocal ? 'payment-proofs/default.png' : null,
            'notes' => $isLocal ? $this->faker->optional()->sentence() : null,
        ];
    }

    /**
     * A completed transaction (paid in full and reconciled).
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => PaymentTransactionStatus::Completed,
        ]);
    }
}
