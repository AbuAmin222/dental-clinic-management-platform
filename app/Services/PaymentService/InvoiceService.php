<?php

declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Enums\InvoiceStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Pricing;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create an invoice as a draft; explicit status transitions must be requested
     * separately via transitionTo() rather than guessed from paid/total on creation.
     *
     * @param array<string, mixed> $data
     * @param int|null $doctorId
     */
    public function createInvoice(array $data, ?int $doctorId = null): Invoice
    {
        return DB::transaction(function () use ($data, $doctorId) {
            $invoice = Invoice::create([
                'patient_id'     => $data['patient_id'],
                'doctor_id'      => $doctorId ?? $data['doctor_id'],
                'appointment_id' => $data['appointment_id'] ?? null,
                'tax_amount'     => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'status'         => InvoiceStatus::Draft,
                'due_date'       => $data['due_date'] ?? null,
            ]);

            $invoice->recalculateTotals();

            return $invoice;
        });
    }

    /**
     * Create or update the single invoice tied to a given appointment, and fully sync its
     * line items to the submitted array (create/update/delete-diff). Totals are never set
     * directly — InvoiceItemObserver recalculates them as a side effect of the item writes.
     *
     * @param array<string, mixed> $data Expects 'items' => array<{pricing_id?, item_name?, quantity, unit_price}>
     */
    public function upsertForAppointment(array $data, Appointment $appointment): Invoice
    {
        return DB::transaction(function () use ($data, $appointment) {
            $invoice = Invoice::firstOrNew(['appointment_id' => $appointment->id]);

            $invoice->fill([
                'doctor_id'       => $appointment->doctor_id,
                'patient_id'      => $appointment->patient_id,
                'tax_amount'      => $data['tax_amount'] ?? $invoice->tax_amount ?? 0,
                'discount_amount' => $data['discount_amount'] ?? $invoice->discount_amount ?? 0,
                'due_date'        => $data['due_date'] ?? $invoice->due_date,
            ]);

            if (! $invoice->exists) {
                $invoice->status = InvoiceStatus::Draft;
            }

            $invoice->save();

            $this->syncItems($invoice, $data['items'] ?? []);

            return $invoice->refresh();
        });
    }

    /**
     * Reconciles an invoice's line items with the submitted array. Existing items not
     * present in the submission (matched by id, when provided) are deleted; the rest are
     * updated or created. Every write fires InvoiceItemObserver, which keeps the parent
     * invoice's totals correct incrementally rather than requiring one bulk recalculation.
     *
     * @param array<int, array<string, mixed>> $items
     */
    public function syncItems(Invoice $invoice, array $items): void
    {
        $submittedIds = [];

        foreach ($items as $itemData) {
            $pricingName = null;

            if (! empty($itemData['pricing_id'])) {
                $pricingName = Pricing::whereKey($itemData['pricing_id'])->value('service_name');
            }

            $item = $invoice->items()->updateOrCreate(
                ['id' => $itemData['id'] ?? null],
                [
                    'pricing_id' => $itemData['pricing_id'] ?? null,
                    'item_name'  => $itemData['item_name'] ?? $pricingName ?? 'Custom item',
                    'quantity'   => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]
            );

            $submittedIds[] = $item->id;
        }

        $invoice->items()->whereNotIn('id', $submittedIds)->delete();
    }

    /**
     * Record a payment against an existing invoice.
     *
     * @throws BusinessRuleViolationException If the payment would exceed the remaining due amount.
     */
    public function recordPayment(Invoice $invoice, float $amount): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount) {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::where('id', $invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($amount > $lockedInvoice->due_amount) {
                throw new BusinessRuleViolationException(
                    __('Payment exceeds remaining unpaid invoice balance.')
                );
            }

            $lockedInvoice->recordPayment($amount);

            return $lockedInvoice->refresh();
        });
    }
}
