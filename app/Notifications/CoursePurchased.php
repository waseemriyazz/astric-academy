<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoursePurchased extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public Course $course,
        public bool $isNewUser = false,
        public ?string $password = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $portalUrl = config('app.portal_url', 'http://localhost:8000');
        $appName = config('app.name');
        $loginUrl = $portalUrl . '/login?email=' . urlencode($notifiable->email);

        $mail = (new MailMessage)
            ->subject('Welcome to ' . $appName . ' — Your Enrollment is Confirmed!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for choosing ' . $appName . '. Your enrollment has been successfully confirmed.')
            ->line('---')
            ->line('**Course:** ' . $this->course->title)
            ->line('**Transaction ID:** ' . $this->payment->txnid)
            ->line('**Amount Paid:** ₹' . number_format((float) $this->payment->amount, 2))
            ->line('**Date:** ' . $this->payment->created_at->format('F j, Y, g:i a'))
            ->line('---');

        if ($this->isNewUser && $this->password) {
            $mail->line('**Your Student Account Has Been Created**')
                ->line('You can now log in to your student portal using the credentials below:')
                ->line('**Email:** ' . $notifiable->email)
                ->line('**Password:** ' . $this->password)
                ->line('Please change your password after logging in for the first time.')
                ->line('---');
        }

        $mail->line('You can access your course materials and start learning from your student dashboard.')
            ->action('Login to Your Dashboard', $loginUrl)
            ->line('If you have any questions, feel free to contact our support team at support@astryxacademy.com.')
            ->line('---')
            ->line('Best regards,')
            ->line('The ' . $appName . ' Team');

        return $mail;
    }
}