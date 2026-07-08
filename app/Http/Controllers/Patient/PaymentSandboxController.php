<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentSandboxController extends Controller
{
    public function showGateway(Request $request)
    {
        $gateway = $request->get('gateway', 'bop');
        $amount = $request->get('amount', 0);
        $tx = $request->get('tx');

        $names = [
            'bop'         => 'Bank of Palestine (Visa/MasterCard)',
            'jawwal_pay'  => 'Jawwal Pay Digital Wallet',
            'palpay'      => 'PalPay Electronic System',
            'paypal'      => 'PayPal Global Gateway'
        ];

        return Inertia::render('Payment/Sandbox', [
            'gatewayName' => $names[$gateway] ?? 'Secure Gateway',
            'gateway'     => $gateway,
            'amount'      => $amount,
            'tx'          => $tx
        ]);
    }
}
