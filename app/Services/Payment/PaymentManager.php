<?php

namespace App\Services\Payment;

use InvalidArgumentException;

class PaymentManager
{
    /**
     * حل وتجهيز الاستراتيجية المناسبة بناءً على اختيار المريض من واجهة Vue
     */
    public static function make(string $method): PaymentStrategy
    {
        return match ($method) {
            'visa', 'mastercard' => new BankOfPalestineCardService(),
            'jawwal_pay'         => new JawwalPayService(),
            'palpay'             => new PalPayService(),
            'paypal'             => new PayPalService(),
            default              => throw new InvalidArgumentException("وسيلة الدفع المحددة غير مدعومة في نظام العيادة حالياً."),
        };
    }
}
