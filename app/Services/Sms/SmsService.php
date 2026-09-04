<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Contracts\Sms\SmsGatewayInterface;
use App\Contracts\Sms\SmsServiceInterface;
use App\Exceptions\Sms\InvalidPhoneNumberException;

/**
 * التطبيق الملموس الوحيد لـ {@see SmsServiceInterface} — الواجهة الأمامية التي يستهلكها
 * بقية المشروع (القنوات، الإشعارات...). مسؤوليته الوحيدة (SRP): تطبيع رقم الهاتف من
 * الصيغة المحلية الفلسطينية إلى الصيغة الدولية E.164، ثم تفويض الإرسال الفعلي إلى
 * {@see SmsGatewayInterface} المُحقَن (Dependency Injection) — تماماً بنفس الدور الذي
 * تلعبه `FileStorageService` مع `FileNamingStrategyInterface` في نطاق التخزين.
 *
 * The single concrete implementation of {@see SmsServiceInterface} — the facade the
 * rest of the project consumes (channels, notifications...). Its single responsibility
 * (SRP): normalize the phone number from the local Palestinian format to international
 * E.164 format, then delegate the actual send to the injected
 * {@see SmsGatewayInterface} — mirroring exactly the role `FileStorageService` plays
 * with `FileNamingStrategyInterface` in the storage domain.
 */
final class SmsService implements SmsServiceInterface
{
    public function __construct(
        private readonly SmsGatewayInterface $gateway,
        private readonly string $defaultCountryCode = '+970',
    ) {}

    public function send(string $phoneNumber, string $message): bool
    {
        return $this->gateway->send(
            $this->normalize($phoneNumber),
            $message,
        );
    }

    /**
     * تطبيع رقم هاتف محلي (`0591234567`) أو دولي (`+970591234567`) إلى صيغة E.164 موحّدة.
     *
     * Normalize a local (`0591234567`) or international (`+970591234567`) phone number
     * into a unified E.164 format.
     *
     * @throws InvalidPhoneNumberException إذا تعذّر التطبيع (أرقام غير كافية بعد التنظيف).
     */
    private function normalize(string $phoneNumber): string
    {
        $digitsOnly = preg_replace('/\D+/', '', $phoneNumber) ?? '';

        if ($digitsOnly === '') {
            throw new InvalidPhoneNumberException("Phone number [{$phoneNumber}] contains no digits and cannot be normalized.");
        }

        // بالفعل بصيغة دولية (يبدأ الرقم الأصلي بـ + قبل التنظيف)
        // Already in international format (original number started with + before cleanup)
        if (str_starts_with($phoneNumber, '+')) {
            return '+' . $digitsOnly;
        }

        // صيغة محلية فلسطينية تبدأ بصفر (059/056...) — نُسقط الصفر ونُلحق مفتاح الدولة
        // Local Palestinian format starting with zero (059/056...) — drop the zero and
        // prepend the country code.
        if (str_starts_with($digitsOnly, '0')) {
            return $this->defaultCountryCode . substr($digitsOnly, 1);
        }

        // آخر احتمال منطقي: أرقام بلا بادئة صفر أو + — نفترض أنها محلية بدون الصفر البادئ
        // Last reasonable fallback: digits with neither a leading zero nor a + — assume
        // it is local without the leading zero.
        return $this->defaultCountryCode . $digitsOnly;
    }
}
