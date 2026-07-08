<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * معالجة بناء الإيميل وإرساله داخل الطابور الخلفي.
     */
    public function toMail($notifiable): MailMessage
    {
        // توليد الرابط الموقع المشفر الخاص بلارافل تلقائياً عبر الكلاس الأب
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Critical: Verify Your Dental Clinic Account Access')
            ->greeting('Welcome to the Dental Clinic Platform,')
            ->line('To finalize your professional account setup and activate your clinical dashboard credentials, please verify your email address by clicking the secure link below.')
            ->action('Verify Account Email Address', $verificationUrl)
            ->line('If you did not initiate this registration on our platform, please ignore this email or contact our system administrator immediately.')
            ->salutation("Best Regards,\nDental Clinic Operations Team");
    }
}
