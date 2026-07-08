<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedResetPassword extends BaseResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * معالجة بناء إيميل استعادة كلمة المرور وإرساله داخل الطابور الخلفي.
     */
    public function toMail($notifiable): MailMessage
    {
        // بناء رابط الاستعادة الموجه لصفحة الواجهة الأمامية باستخدام التوكن الممرر للكلاس
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Security Alert: Reset Your Password Request')
            ->greeting('Hello,')
            ->line('We received a formal request to reset the password associated with your Dental Clinic account.')
            ->action('Reset Secure Password', $url)
            ->line('This recovery link is strictly valid for 60 minutes. If you did not make this request, your account credentials remain safe and no further action is required.')
            ->salutation("Security & Integrity Team,\nDental Clinic Platform");
    }
}
