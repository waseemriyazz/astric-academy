<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNewStudent extends Notification
{
    use Queueable;

    public function __construct(
        public string $password
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' - Your Account Details')
            ->greeting('Welcome ' . $notifiable->name . '!')
            ->line('Your student account has been created successfully.')
            ->line('You can log in using the following credentials:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->password)
            ->action('Log in to Your Dashboard', url('/login'))
            ->line('We recommend changing your password after your first login.')
            ->line('Start exploring your courses and begin your learning journey!')
            ->line('If you have any questions, please contact our support team.');
    }
}