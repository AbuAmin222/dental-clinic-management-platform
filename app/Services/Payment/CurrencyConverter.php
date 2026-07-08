<?php

namespace App\Services\Payment;

/**
 * Every invoice in this clinic is denominated in ILS. PayPal settles in USD,
 * so anything routed through PayPal needs converting both ways:
 *   - ilsToUsd(): what to actually charge the patient on PayPal
 *   - usdToIls(): how much of the ILS balance that USD charge paid off
 *
 * The rate is a static, manually-configured value for now (see
 * config/services.php -> 'exchange.ils_to_usd'). That's a deliberate
 * simplification while the gateways are sandboxed - swap the body of
 * rate() for a live FX API call later and nothing else in the app needs
 * to change, since every caller goes through this one class.
 */
class CurrencyConverter
{
    public function ilsToUsd(float $amountIls): float
    {
        return round($amountIls * $this->rate(), 2);
    }

    public function usdToIls(float $amountUsd): float
    {
        return round($amountUsd / $this->rate(), 2);
    }

    protected function rate(): float
    {
        return (float) config('services.exchange.ils_to_usd', 0.27);
    }
}
