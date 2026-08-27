<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\PaymentMethod;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentMethodTest extends TestCase
{
    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('visa', PaymentMethod::Visa->value);
        $this->assertSame('mastercard', PaymentMethod::Mastercard->value);
        $this->assertSame('bop', PaymentMethod::BankOfPalestine->value);
        $this->assertSame('paypal', PaymentMethod::PayPal->value);
        $this->assertSame('jawwal_pay', PaymentMethod::JawwalPay->value);
        $this->assertSame('palpay', PaymentMethod::PalPay->value);
        $this->assertSame('local_transfer', PaymentMethod::LocalTransfer->value);
    }

    #[Test]
    public function automated_gateways_excludes_local_transfer(): void
    {
        $gateways = PaymentMethod::automatedGateways();

        $this->assertNotContains(PaymentMethod::LocalTransfer, $gateways);
        $this->assertCount(6, $gateways);
    }

    #[Test]
    public function automated_gateway_values_returns_string_values(): void
    {
        $values = PaymentMethod::automatedGatewayValues();

        $this->assertSame(['visa', 'mastercard', 'bop', 'paypal', 'jawwal_pay', 'palpay'], $values);
    }

    #[Test]
    public function palestinian_origin_includes_local_methods(): void
    {
        $palestinian = PaymentMethod::palestinianOrigin();

        $this->assertContains(PaymentMethod::JawwalPay, $palestinian);
        $this->assertContains(PaymentMethod::PalPay, $palestinian);
        $this->assertContains(PaymentMethod::BankOfPalestine, $palestinian);
    }

    #[Test]
    public function international_origin_includes_global_gateways(): void
    {
        $international = PaymentMethod::internationalOrigin();

        $this->assertContains(PaymentMethod::Visa, $international);
        $this->assertContains(PaymentMethod::Mastercard, $international);
        $this->assertContains(PaymentMethod::PayPal, $international);
    }

    #[Test]
    public function label_returns_correct_display_name(): void
    {
        $this->assertSame('Bank of Palestine — Visa/Mastercard', PaymentMethod::Visa->label());
        $this->assertSame('Bank of Palestine — Visa/Mastercard', PaymentMethod::Mastercard->label());
        $this->assertSame('Bank of Palestine — Visa/Mastercard', PaymentMethod::BankOfPalestine->label());
        $this->assertSame('PayPal (International Gateway)', PaymentMethod::PayPal->label());
        $this->assertSame('Jawwal Pay', PaymentMethod::JawwalPay->label());
        $this->assertSame('PalPay - Mahfazti', PaymentMethod::PalPay->label());
        $this->assertSame('Local payment transfare (Manual Review)', PaymentMethod::LocalTransfer->label());
    }
}
