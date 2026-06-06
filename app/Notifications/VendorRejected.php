<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorRejected extends Notification
{
    use Queueable;

    protected $reason;

    public function __construct($reason = null)
    {
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Your vendor application has been reviewed')
            ->greeting('Hello '.$notifiable->name)
            ->line('We reviewed your vendor application and, unfortunately, it was not approved at this time.');

        if ($this->reason) {
            $mail->line('Reason: ' . $this->reason);
        }

        $mail->line('You may update your application and re-submit when ready.');

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your vendor application was rejected.',
            'reason' => $this->reason,
        ];
    }
}
