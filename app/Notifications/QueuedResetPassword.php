<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedResetPassword extends BaseResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * Build the secure text layout password reset notification mail message inside async queues.
     *
     * @param  mixed  $notifiable  The user entity instance receiving the notification.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('🛡️ Security Alert: Account Password Reset Request')
            ->greeting("Hello, {$notifiable->first_name}!")
            ->line('We received a formal automated request to reset the password associated with your Dental Clinic Application profile.')
            ->action('Reset Secure Password', $url)
            ->line('Security Note: This recovery authorization link is strictly active for 60 minutes.')
            ->line('If you did not initiate this system request, your security credentials remain perfectly safe. No further action is required.')
            ->salutation("Best Regards,\nSecurity & Integrity Architecture Team\nDental Clinic Platform");
    }
}
