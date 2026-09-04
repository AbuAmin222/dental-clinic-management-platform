<?php

declare(strict_types=1);

namespace App\Contracts\Sms;

/**
 * العقد الذي يجب أن يطبّقه أي "مزوّد" (Driver) فعلي لإرسال رسائل SMS — تماماً بنفس دور
 * `FileNamingStrategyInterface` في نطاق التخزين: نقطة التوسعة الوحيدة (OCP) لإضافة مزوّد
 * جديد (Twilio، مزوّد فلسطيني محلي، ...) دون تعديل أي كود آخر في المشروع — يكفي كتابة
 * كلاس جديد يطبّق هذا العقد وربطه في `SmsServiceProvider`.
 *
 * The contract any concrete SMS "driver" must implement — mirrors the role of
 * `FileNamingStrategyInterface` in the storage domain: the single extension point (OCP)
 * for adding a new provider (Twilio, a local Palestinian gateway, ...) without touching
 * any other code in the project — just write a new class implementing this contract and
 * bind it in `SmsServiceProvider`.
 */
interface SmsGatewayInterface
{
    /**
     * إرسال رسالة SMS فعلية إلى رقم هاتف بصيغة دولية (E.164، مثال: `+970591234567`).
     * الاستدعاء الآمن لأرقام محلية (`059...`) هو عبر {@see \App\Contracts\Sms\SmsServiceInterface::send()}،
     * الذي يطبّع الرقم أولاً قبل تمريره لهذا العقد.
     *
     * Send an actual SMS message to a phone number in international format (E.164,
     * e.g. `+970591234567`). Safe calling for local-format numbers (`059...`) is via
     * {@see \App\Contracts\Sms\SmsServiceInterface::send()}, which normalizes the number
     * first before delegating to this contract.
     *
     * @param string $internationalPhoneNumber رقم الهاتف بصيغة E.164 / Phone number in E.164 format.
     * @param string $message                  نص الرسالة / The message body.
     * @return bool                            true عند نجاح التسليم (أو القبول من طرف المزوّد) / true on successful delivery (or provider acceptance).
     *
     * @throws \App\Exceptions\Sms\SmsDeliveryException إذا فشل الإرسال (خطأ شبكة، رفض من المزوّد، بيانات اعتماد غير صالحة...).
     */
    public function send(string $internationalPhoneNumber, string $message): bool;
}
