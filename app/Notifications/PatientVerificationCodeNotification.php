<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * يُرسل رمز التحقق الرقمي المطلوب لإكمال إجراءات الأمان لحساب مريض أنشأه الاستقبال.
 *
 * ⚠️ قناة الإرسال الحالية بريد إلكتروني (`toMail`) — القناة العملية الأصح لهذا النوع من
 * الرموز هي رسالة SMS مباشرة على الهاتف (تماماً كما تصفه الوثيقة المعمارية: "رمز تأكيد
 * مُرسَل له")، لكن لا يوجد أي مزوّد SMS مُهيَّأ في المشروع بعد (لا Twilio ولا مزوّد محلي
 * فلسطيني). البنية جاهزة تماماً للتبديل لاحقاً: أضف `toSms()` وأدرج 'sms' في `via()` بمجرد
 * اختيار مزوّد فعلي — لا حاجة لتعديل أي شيء آخر في تدفق العمل نفسه (Service/Middleware/
 * Controller كلها تستدعي `$user->notify()` فقط، لا تعرف تفاصيل القناة إطلاقاً).
 */
class PatientVerificationCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly int $expiryMinutes,
    ) {}

    /** @return array<int, string> */
    public function via(mixed $notifiable): array
    {
        return ['sms'];
    }

    public function toSms(mixed $notifiable): string
    {
        return "Dental Clinic Application: Your verification code is {$this->code}. It expires in {$this->expiryMinutes} minutes. Do not share this code with anyone.";

        // return (new MailMessage)
        //     ->subject('🔐 Your Account Verification Code')
        //     ->greeting("Hello, {$notifiable->first_name}!")
        //     ->line('Before you can access any clinic services, please confirm your identity using the verification code below.')
        //     ->line("Verification Code: {$this->code}")
        //     ->line("This code will expire in {$this->expiryMinutes} minutes.")
        //     ->line('If you did not request this code, please contact the clinic front desk immediately.')
        //     ->salutation("Yours Sincerely,\nDental Clinic Application (DCA)");
    }
}
