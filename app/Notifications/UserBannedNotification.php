<?php
// © Atia Hegazy — atiaeno.com

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserBannedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $banType,
        private string $reason,
        private ?\Carbon\Carbon $expiresAt = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Account ' . ($this->banType === 'hard' ? 'Permanently Banned' : 'Suspended'))
            ->line('Your account has been ' . ($this->banType === 'hard' ? 'permanently banned' : 'suspended') . '.')
            ->line('Reason: ' . $this->reason);

        if ($this->expiresAt) {
            $mail->line('Your suspension will expire on: ' . $this->expiresAt->toDateTimeString());
        }

        $mail->line('If you believe this was done in error, please contact support.');

        return $mail;
    }
}
