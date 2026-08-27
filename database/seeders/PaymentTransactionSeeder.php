<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Models\Invoice;
use App\Models\LocalPaymentMethod;
use App\Models\PaymentTransaction;
use Illuminate\Database\Seeder;

class PaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $paidInvoices = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Paid, InvoiceStatus::PartiallyPaid])
            ->whereDoesntHave('paymentTransactions')
            ->get();

        if ($paidInvoices->isEmpty()) {
            $this->command?->warn('⚠️ No paid/partially-paid invoices without a transaction found — skipping PaymentTransactionSeeder. Run InvoiceSeeder first.');
            return;
        }

        $localPaymentMethods = LocalPaymentMethod::all();

        foreach ($paidInvoices as $invoice) {
            $method = fake()->randomElement(PaymentMethod::cases());
            $isLocal = $method === PaymentMethod::LocalTransfer;
            $localMethod = $isLocal && $localPaymentMethods->isNotEmpty() ? $localPaymentMethods->random() : null;

            PaymentTransaction::create([
                'invoice_id' => $invoice->id,
                'local_payment_method_id' => $localMethod?->id,
                'transaction_id' => $isLocal ? null : fake()->unique()->uuid(),
                'transaction_reference' => $isLocal
                    ? 'LOCAL-' . fake()->unique()->numberBetween(10000, 99999)
                    : fake()->unique()->bothify('REF-########'),
                'payment_method' => $method,
                'amount' => $invoice->paid_amount,
                'currency' => 'ILS',
                'status' => PaymentTransactionStatus::Completed,
                'gateway_response' => $isLocal ? null : [
                    'gateway' => $method->value,
                    'reference' => fake()->uuid(),
                    'result' => 'approved',
                ],
                'proof_image_path' => $isLocal ? 'payment-proofs/default.png' : null,
                'notes' => null,
            ]);
        }

        $this->command?->info("✅ PaymentTransactionSeeder: created transactions for {$paidInvoices->count()} invoice(s).");
    }
}
