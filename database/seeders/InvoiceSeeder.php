<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\InvoiceStatus;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Pricing;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    private int $targetCount;

    public function __construct()
    {
        $this->targetCount = (int) config('clinic.service_count.CLINIC_INVOICE_COUNT', 10);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $billableAppointments = Appointment::query()
            ->where('status', AppointmentStatus::Completed)
            ->whereDoesntHave('invoice')
            ->inRandomOrder()
            ->limit($this->targetCount)
            ->get();

        if ($billableAppointments->isEmpty()) {
            $this->command?->warn('⚠️ No completed, un-invoiced appointments found — skipping InvoiceSeeder. Run AppointmentSeeder first.');
            return;
        }

        foreach ($billableAppointments as $appointment) {
            $invoice = Invoice::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id' => $appointment->doctor_id,
                    'patient_id' => $appointment->patient_id,
                    'due_date' => now()->addWeeks(2),
                    'status'     => InvoiceStatus::Pending,
                ]
            );

            if ($invoice->items()->exists()) {
                // Already fully seeded on a previous run — skip regenerating items/payment.
                continue;
            }

            $pricingOptions = Pricing::where('doctor_id', $appointment->doctor_id)->get();
            $itemCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $itemCount; $i++) {
                $pricing = $pricingOptions->isNotEmpty() ? $pricingOptions->random() : null;

                $quantity = fake()->numberBetween(1, 2);
                $unitPrice = $pricing?->amount ?? (fake()->numberBetween(50, 400) * 100);
                $totalPrice = $quantity * $unitPrice;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'pricing_id' => $pricing?->id,
                    'item_name' => $pricing?->service_name ?? fake()->randomElement(['Cleaning', 'Filling', 'X-Ray', 'Whitening']),
                    'quantity' => fake()->numberBetween(1, 2),
                    'unit_price' => $pricing?->amount ?? fake()->randomFloat(2, 50, 400),
                    'total_price' => $totalPrice,
                ]);
            }

            $invoice->recalculateTotals();

            // Simulate real-world payment progress for roughly two-thirds of invoices.
            if (fake()->boolean(65)) {
                $paymentPortion = fake()->randomElement([1.0, 0.5]);
                $invoice->recordPayment(round($invoice->total_amount * $paymentPortion, 2));
            }
        }

        $this->command?->info("✅ InvoiceSeeder: ensured invoices (with line items) for {$billableAppointments->count()} completed appointment(s).");
    }
}
