<?php

declare(strict_types=1);

namespace App\Services\Sms\Drivers;

use App\Contracts\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Log;

/**
 * ⚠️ مزوّد افتراضي آمن للتطوير المحلي فقط (Dev-Safe Default Driver) — لا يُرسل أي رسالة
 * SMS حقيقية إطلاقاً؛ يكتفي بتسجيل محتوى الرسالة في سجلّات Laravel (Log)، تماماً كما
 * يفعل driver البريد `log` في Laravel نفسه. هذا هو الـ driver الافتراضي
 * (`SMS_DRIVER=log`) طالما لم يُختَر مزوّد SMS حقيقي بعد.
 *
 * لا تستخدم هذا الـ driver في بيئة الإنتاج (Production) — رمز التحقق لن يصل لأي مستخدم
 * فعلياً، وسيبقى محبوساً في السجلّات فقط. عند اختيار مزوّد حقيقي (Twilio، مزوّد فلسطيني
 * محلي، ...)، يكفي إنشاء كلاس جديد يطبّق {@see \App\Contracts\Sms\SmsGatewayInterface}
 * وربطه في `SmsServiceProvider` — لا حاجة لتعديل أي كود آخر في المشروع (OCP).
 *
 * ⚠️ Dev-safe default driver, local development ONLY — never sends a real SMS message;
 * it simply logs the message content to Laravel's log, exactly like Laravel's own `log`
 * mail driver. This is the default driver (`SMS_DRIVER=log`) as long as no real SMS
 * provider has been chosen yet.
 *
 * Do NOT use this driver in Production — the verification code will never actually
 * reach any user, it will stay trapped in the logs only. Once a real provider is chosen
 * (Twilio, a local Palestinian gateway, ...), simply create a new class implementing
 * {@see \App\Contracts\Sms\SmsGatewayInterface} and bind it in `SmsServiceProvider` — no
 * other code in the project needs to change (OCP).
 */
final class LogSmsGateway implements SmsGatewayInterface
{
    public function __construct(private readonly string $logChannel = 'stack') {}

    public function send(string $internationalPhoneNumber, string $message): bool
    {
        Log::channel($this->logChannel)->info('[SMS:log-driver] Outbound message (NOT actually delivered)', [
            'to' => $internationalPhoneNumber,
            'message' => $message,
        ]);

        return true;
    }
}
