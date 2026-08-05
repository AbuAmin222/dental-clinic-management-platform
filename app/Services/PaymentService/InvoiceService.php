<?php

declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Exceptions\BusinessRuleViolationException;
use App\Models\Appointment;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

/**
 * Class InvoiceService
 * Handles financial ledger transactions, balance calculations, and billing generation.
 */
class InvoiceService
{
    /**
     * Create invoice record wrapped in a database transaction.
     *
     * @param array<string, mixed> $data
     * @param int|null $doctorId
     * @return Invoice
     */
    public function createInvoice(array $data, ?int $doctorId = null): Invoice
    {
        return DB::transaction(function () use ($data, $doctorId) {
            $total = (float) $data['total_amount'];
            $paid = (float) ($data['paid_amount'] ?? 0.00);

            $status = $data['status'] ?? match (true) {
                $paid >= $total => 'paid',
                $paid > 0       => 'partially_paid',
                default         => 'unpaid',
            };

            return Invoice::create([
                'patient_id'     => $data['patient_id'],
                'doctor_id'      => $doctorId ?? $data['doctor_id'],
                'appointment_id' => $data['appointment_id'] ?? null,
                'total_amount'   => $total,
                'paid_amount'    => $paid,
                'status'         => $status,
                'due_date'       => $data['due_date'],
            ]);
        });
    }

    /**
     * Create or update the single invoice tied to a given appointment.
     *
     * Added to support the confirmed one-to-one Appointment<->Invoice business rule
     * (see DECISIONS_LOG.md / PENDING_TASKS.md): a receptionist may open the invoice
     * form for an appointment that already has an invoice (edit case) or does not yet
     * have one (create case) — both must funnel through this single, scoped operation
     * instead of Controllers issuing raw Eloquent calls directly.
     *
     * @param array<string, mixed> $data
     * @param Appointment $appointment
     * @return Invoice
     */
    public function upsertForAppointment(array $data, Appointment $appointment): Invoice
    {
        return DB::transaction(function () use ($data, $appointment) {
            $total = (float) $data['total_amount'];
            $paid = (float) $data['paid_amount'];

            return Invoice::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id'    => $appointment->doctor_id,
                    'patient_id'   => $appointment->patient_id,
                    'total_amount' => $total,
                    'paid_amount'  => $paid,
                    'status'       => $data['status'],
                    'due_date'     => $data['due_date'],
                ]
            );
        });
    }

    /**
     * Record dynamic payment against an existing invoice.
     *
     * HIGH ROBUSTNESS FIX: the invoice row is re-fetched with a pessimistic lock
     * (SELECT ... FOR UPDATE) inside the transaction before the balance check.
     * Without this lock, two concurrent payment requests against the same invoice
     * could both pass the balance-check read before either write is committed,
     * allowing paid_amount to exceed total_amount (a real overpayment bug).
     *
     * @param Invoice $invoice
     * @param float $amount
     * @return Invoice
     * @throws BusinessRuleViolationException If the payment would exceed the remaining unpaid balance.
     */
    public function recordPayment(Invoice $invoice, float $amount): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount) {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::where('id', $invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedInvoice->paid_amount + $amount > $lockedInvoice->total_amount) {
                throw new BusinessRuleViolationException(
                    __('Payment exceeds remaining unpaid invoice balance.')
                );
            }

            $lockedInvoice->pay($amount);

            return $lockedInvoice->refresh();
        });
    }
}
