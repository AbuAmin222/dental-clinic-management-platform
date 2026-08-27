<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Financial;
use App\Models\LocalPaymentMethod;
use Illuminate\Database\Seeder;

/**
 * SEEDER GAP FIX: no seeder existed for LocalPaymentMethod even after the schema was
 * fixed to match the Model (see the 2026_08_21 audit-fix migration). Idempotent via
 * firstOrCreate keyed on (financial_id, title).
 */
class LocalPaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $financials = Financial::all();

        if ($financials->isEmpty()) {
            $this->command?->warn('⚠️ No financial officers found — skipping LocalPaymentMethodSeeder. Run FinancialSeeder first.');
            return;
        }

        foreach ($financials as $financial) {
            LocalPaymentMethod::firstOrCreate(
                ['financial_id' => $financial->id, 'title' => 'Bank of Palestine — Local Transfer'],
                [
                    'account_number' => fake()->numerify('##########'),
                    'iban' => 'PS' . fake()->numerify('##################'),
                    'is_visible_to_patient' => true,
                    'is_active' => true,
                ]
            );

            LocalPaymentMethod::firstOrCreate(
                ['financial_id' => $financial->id, 'title' => 'JawwalPay Wallet'],
                [
                    'bank_phone_number' => fake()->numerify('059#######'),
                    'is_visible_to_patient' => true,
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info("✅ LocalPaymentMethodSeeder: payment methods ensured for {$financials->count()} financial officer(s).");
    }
}
