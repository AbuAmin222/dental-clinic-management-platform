<?php

declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Models\Invoice;
use App\Models\User;
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
     * Record dynamic payment against an existing invoice.
     *
     * @param Invoice $invoice
     * @param float $amount
     * @return Invoice
     */
    public function recordPayment(Invoice $invoice, float $amount): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount) {
            if ($invoice->paid_amount + $amount > $invoice->total_amount) {
                throw new \DomainException(__('Payment exceeds remaining unpaid invoice balance.'));
            }

            $invoice->pay($amount);

            return $invoice->refresh();
        });
    }
}
