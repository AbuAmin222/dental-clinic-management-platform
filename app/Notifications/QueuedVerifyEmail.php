<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Build the verification email message layout and process dispatch configurations inside background queues.
     *
     * @param  mixed  $notifiable  The user entity instance requiring authentication verification.
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('📨 Action Required: Activate Your Dental Clinic Account')
            ->greeting("Welcome aboard, {$notifiable->first_name}!")
            ->line('Thank you for registering. To complete your professional medical profile setup and unlock your clinic dashboard, please verify your email address.')
            ->action('Activate Account & Verify Email', $verificationUrl)
            ->line('If you did not execute this registration on ourntem administration safely.')
            ->salutation("Yours Sincerely,\nOperations & Patient Care Team\nDental Clinic Application (DCA)");
    }
}
