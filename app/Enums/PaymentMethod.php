<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case Visa = 'visa';
    case Mastercard = 'mastercard';
    case BankOfPalestine = 'bop';
    case PayPal = 'paypal';
    case JawwalPay = 'jawwal_pay';
    case PalPay = 'palpay';
    case LocalTransfer = 'local_transfer';

    public static function automatedGateways(): array
    {
        return [self::Visa, self::Mastercard, self::BankOfPalestine, self::PayPal, self::JawwalPay, self::PalPay];
    }

    /** @return string[] */
    public static function automatedGatewayValues(): array
    {
        return array_map(fn(self $m) => $m->value, self::automatedGateways());
    }

    public static function palestinianOrigin(): array
    {
        return [self::JawwalPay, self::PalPay, self::BankOfPalestine];
    }

    public static function internationalOrigin(): array
    {
        return [self::Visa, self::Mastercard, self::PayPal];
    }

    public function label(): string
    {
        return match ($this) {
            self::Visa, self::Mastercard, self::BankOfPalestine
            => 'Bank of Palestine — Visa/Mastercard',
            self::PayPal        => 'PayPal (International Gateway)',
            self::JawwalPay     => 'Jawwal Pay',
            self::PalPay        => 'PalPay - Mahfazti',
            self::LocalTransfer => 'Local payment transfare (Manual Review)',
        };
    }
}
