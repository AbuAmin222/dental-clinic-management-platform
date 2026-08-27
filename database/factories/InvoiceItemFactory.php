<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Pricing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pricing = Pricing::inRandomOrder()->first();

        return [
            'invoice_id' => Invoice::factory(),
            'pricing_id' => $pricing?->id,
            'item_name' => $pricing?->service_name ?? $this->faker->randomElement(['Cleaning', 'Filling', 'X-Ray', 'Whitening', 'Consultation']),
            'quantity' => $this->faker->numberBetween(1, 2),
            'unit_price' => $pricing?->amount ?? $this->faker->randomFloat(2, 50, 400),
        ];
    }
}
