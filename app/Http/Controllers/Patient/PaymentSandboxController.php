<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Class PaymentSandboxController
 *
 * Implements sandbox terminals for the simulated gateway confirmation screen.
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
        $gateway = (string) $request->get('gateway', PaymentMethod::Visa->value);
        $amount = (string) $request->get('amount', '0');
        $tx = (string) $request->get('tx');
        $gatewayLabel = PaymentMethod::tryFrom($gateway)?->label() ?? 'Secure Gateway';

        return Inertia::render('Payment/Sandbox', [
            'gatewayName' => $gatewayLabel,
            'gateway'     => $gateway,
            'amount'      => $amount,
            'tx'          => $tx
        ]);
    }
}
