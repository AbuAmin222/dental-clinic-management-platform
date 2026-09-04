<?php

declare(strict_types=1);

namespace App\Contracts\Sms;

/**
 * الواجهة الأمامية (Facade Contract) لإرسال SMS التي يستخدمها بقية المشروع — تماماً بنفس
 * دور `FileStorageServiceInterface` في نطاق التخزين. تتكفّل التطبيقات الملموسة لهذا العقد
 * بتطبيع رقم الهاتف (تحويل الصيغة المحلية الفلسطينية `059.../056...` إلى الصيغة الدولية
 * E.164) قبل تفويض الإرسال الفعلي لـ {@see \App\Contracts\Sms\SmsGatewayInterface}.
 *
 * The facade contract for sending SMS that the rest of the project consumes — mirrors
 * the role of `FileStorageServiceInterface` in the storage domain. Concrete
 * implementations of this contract are responsible for normalizing the phone number
 * (converting the local Palestinian format `059.../056...` to international E.164
 * format) before delegating the actual send to {@see \App\Contracts\Sms\SmsGatewayInterface}.
 */
interface SmsServiceInterface
{
    /**
     * إرسال رسالة SMS إلى رقم هاتف محلي أو دولي — يطبّع الرقم داخلياً قبل الإرسال.
     *
     * Send an SMS message to a local or international phone number — normalizes the
     * number internally before sending.
     *
     * @param string $phoneNumber رقم الهاتف بأي صيغة مدعومة (محلي فلسطيني أو E.164) / Phone number in any supported format (local Palestinian or E.164).
     * @param string $message     نص الرسالة / The message body.
     * @return bool
     *
     * @throws \App\Exceptions\Sms\SmsDeliveryException إذا فشل الإرسال.
     * @throws \App\Exceptions\Sms\InvalidPhoneNumberException إذا كان الرقم غير صالح للتطبيع.
     */
    public function send(string $phoneNumber, string $message): bool;
}
