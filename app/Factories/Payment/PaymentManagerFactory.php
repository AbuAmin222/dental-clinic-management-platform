<?php

declare(strict_types=1);

namespace App\Factories\Payment;

use App\Contracts\Payment\PaymentStrategyInterface;
use App\Services\Payment\BankOfPalestineCardService;
use App\Services\Payment\JawwalPayService;
use App\Services\Payment\PalPayService;
use App\Services\Payment\PayPalService;
use InvalidArgumentException;

class PaymentManagerFactory
{
    /**
     * Strategy registry mapping payment channels to concrete implementation classes.
     *
     * @var array<string, class-string<PaymentStrategyInterface>>
     */
    protected static array $map = [
        'bop'        => BankOfPalestineCardService::class,
        'visa'       => BankOfPalestineCardService::class,
        'mastercard' => BankOfPalestineCardService::class,
        'jawwal_pay' => JawwalPayService::class,
        'palpay'     => PalPayService::class,
        'paypal'     => PayPalService::class,
    ];

    /**
     * Dynamically resolve and construct the requested payment strategy via IoC container.
     *
     * @param string $method
     * @return PaymentStrategyInterface
     *
     * @throws InvalidArgumentException If payment gateway target class is unmapped or missing.
     */
    public static function make(string $method): PaymentStrategyInterface
    {
        $normalizedMethod = strtolower(trim($method));

        if (! isset(self::$map[$normalizedMethod])) {
            throw new InvalidArgumentException(
                "Payment Structural Error: Unsupported payment gateway method [{$method}]."
            );
        }

        $strategyClass = self::$map[$normalizedMethod];

        return app($strategyClass);
    }
}
