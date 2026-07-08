<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Pricing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Show Create/Edit financial invoice for an appointment.
     */
    public function create(Appointment $appointment)
    {
        // Eager load to optimize query performance
        $appointment->load(['patient.user', 'doctor.user']);

        // Explicitly fetch the first record or return null (fixes the empty array [] JS truthy bug)
        $invoice = Invoice::where('appointment_id', $appointment->id)->first();

        $pricings = Pricing::where('doctor_id', $appointment->doctor_id)->get();

        return inertia('Receptionist/Invoices/Create', [
            'appointment' => $appointment,
            'pricings'    => $pricings,
            'invoice'     => $invoice,
        ]);
    }

    /**
     * Save or update the appointment invoice.
     */
    public function store(Request $request, Appointment $appointment)
    {
        $input = $request->all();

        Validator::make($input, [
            'total_amount' => 'required|numeric|min:0',
            'paid_amount'  => 'required|numeric|min:0|max:' . ($input['total_amount'] ?? 0),
            'status'       => 'required|in:paid,unpaid,partially_paid',
            'due_date'     => 'required|date',
        ])->validate();

        DB::transaction(function () use ($input, $appointment) {
            $total = floatval($input['total_amount']);
            $paid = floatval($input['paid_amount']);
            $balance_amount = max(0, $total - $paid);

            // Atomic Upsert operation protecting system records
            Invoice::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id'      => $appointment->doctor_id,
                    'patient_id'     => $appointment->patient_id,
                    'total_amount'   => $total,
                    'paid_amount'    => $paid,
                    'balance_amount' => $balance_amount,
                    'status'         => $input['status'],
                    'due_date'       => Carbon::parse($input['due_date'])->toDateTimeString(),
                ]
            );
        });

        return redirect()->route('receptionist.appointments.index')
            ->with('success', 'Invoice processed successfully!');
    }

    /**
     * Delete the single resource instance.
     */
    public function destroy(Appointment $appointment)
    {
        $invoice = Invoice::where('appointment_id', $appointment->id)->first();

        if ($invoice) {
            $invoice->delete();
            return redirect()->route('receptionist.appointments.index')
                ->with('success', 'Invoice deleted successfully!');
        }

        return redirect()->route('receptionist.appointments.index')
            ->with('error', 'No invoice found to delete.');
    }
}
