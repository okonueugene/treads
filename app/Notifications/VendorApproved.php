<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApproved extends Notification
{
    use Queueable;

    public function __construct()
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your vendor application has been approved')
            ->greeting('Hello '.$notifiable->name)
            ->line('Congratulations — your vendor application has been approved. Your shop is now live and you can list products.')
            ->action('Go to your dashboard', url('/vendor'))
            ->line('Thank you for joining TreadMart!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your vendor application has been approved.',
        ];
    }
}
