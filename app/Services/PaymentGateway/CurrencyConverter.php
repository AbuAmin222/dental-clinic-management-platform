<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Config;

class CurrencyConverter
{
    /**
     * Convert local currency Shekels (ILS) to US Dollars (USD).
     *
     * @param float $amountIls
     * @return float
     */
    public function ilsToUsd(float $amountIls): float
    {
        return round($amountIls * $this->getExchangeRate(), 2);
    }

    /**
     * Convert US Dollars (USD) to Shekels (ILS).
     *
     * @param float $amountUsd
     * @return float
     */
    public function usdToIls(float $amountUsd): float
    {
        $rate = $this->getExchangeRate();

        return $rate > 0 ? round($amountUsd / $rate, 2) : 0.00;
    }

    /**
     * Resolve exchange rate from system configurations.
     *
     * @return float
     */
    protected function getExchangeRate(): float
    {
        return (float) Config::get('services.exchange.ils_to_usd', 0.27);
    }
}
