<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class PaymentSandboxController
 *
 * Implements sandbox terminals, retrieving configurations externally to comply with OCP.
 *
 * @package App\Http\Controllers\Patient
 */
class PaymentSandboxController extends Controller
{
    /**
     * Serve standard simulated interactive dashboard windows.
     *
     * @param Request $request
     * @return InertiaResponse
     */
    public function showGateway(Request $request): InertiaResponse
    {
        $gateway = (string) $request->get('gateway', 'bop');
        $amount = (string) $request->get('amount', '0');
        $tx = (string) $request->get('tx');

        // Retrieved from payment configuration to adhere to Open-Closed Principle (OCP)
        $names = Config::get('payment.sandbox.gateways', [
            'bop'        => 'Bank of Palestine (Visa/MasterCard)',
            'jawwal_pay' => 'Jawwal Pay Digital Wallet',
            'palpay'     => 'PalPay Electronic System',
            'paypal'     => 'PayPal Global Gateway'
        ]);

        return Inertia::render('Payment/Sandbox', [
            'gatewayName' => $names[$gateway] ?? 'Secure Gateway',
            'gateway'     => $gateway,
            'amount'      => $amount,
            'tx'          => $tx
        ]);
    }
}
