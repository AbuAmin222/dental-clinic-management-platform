<?php

declare(strict_types=1);

namespace App\Factories\Payment;

use App\Contracts\Payment\PaymentStrategyInterface;
use App\Enums\PaymentMethod;
use App\Services\PaymentGateway\BankOfPalestineCardService;
use App\Services\PaymentGateway\JawwalPayService;
use App\Services\PaymentGateway\PalPayService;
use App\Services\PaymentGateway\PayPalService;
use InvalidArgumentException;

class PaymentManagerFactory
{
    /**
     * Strategy registry mapping payment channels to concrete implementation classes.
     *
     * @var array<string, class-string<PaymentStrategyInterface>>
     */
    protected static array $map = [
        PaymentMethod::BankOfPalestine->value => BankOfPalestineCardService::class,
        PaymentMethod::Mastercard->value      => BankOfPalestineCardService::class,
        PaymentMethod::Visa->value            => BankOfPalestineCardService::class,
        PaymentMethod::JawwalPay->value       => JawwalPayService::class,
        PaymentMethod::PalPay->value          => PalPayService::class,
        PaymentMethod::PayPal->value          => PayPalService::class,
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
