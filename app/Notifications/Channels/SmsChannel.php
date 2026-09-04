<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Contracts\Sms\SmsServiceInterface;
use Illuminate\Notifications\Notification;
use RuntimeException;

/**
 * قناة إشعارات مخصّصة (Custom Notification Channel) تُسجَّل تحت الاسم `sms` عبر
 * `Notification::extend()` في `SmsServiceProvider`. أي إشعار يريد الإرسال عبر SMS يكفي
 * أن يُدرج `'sms'` في `via()` ويطبّق دالة `toSms(mixed $notifiable): string` — تماماً
 * بنفس آلية القناة المدمجة `mail` في Laravel لكن لرسائل SMS.
 *
 * A custom Notification Channel registered under the name `sms` via
 * `Notification::extend()` in `SmsServiceProvider`. Any notification wanting to send via
 * SMS simply lists `'sms'` in `via()` and implements a `toSms(mixed $notifiable): string`
 * method — mirroring exactly Laravel's built-in `mail` channel mechanism, but for SMS.
 */
final class SmsChannel
{
    public function __construct(private readonly SmsServiceInterface $smsService)
    {
    }

    /**
     * @throws RuntimeException إذا لم يُطبّق الإشعار الممرَّر دالة toSms().
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            throw new RuntimeException(
                get_class($notification) . ' must implement a toSms() method to be sent via the "sms" channel.'
            );
        }

        $phoneNumber = method_exists($notifiable, 'routeNotificationFor')
            ? $notifiable->routeNotificationFor('sms', $notification)
            : ($notifiable->phone ?? null);

        // سياسة صامتة عند غياب رقم هاتف صالح — بنفس فلسفة قناة mail المدمجة في Laravel
        // عندما لا يملك الكائن القابل للإشعار بريداً إلكترونياً.
        // Silent no-op when no valid phone number exists — mirrors the same philosophy
        // Laravel's built-in mail channel follows when the notifiable has no email.
        if (blank($phoneNumber)) {
            return;
        }

        $message = $notification->toSms($notifiable);

        $this->smsService->send((string) $phoneNumber, (string) $message);
    }
}