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
        $mail = (new MailMessage)
            ->subject('Course Purchase Confirmation - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for your purchase. Your enrollment has been confirmed.')
            ->line('**Course:** ' . $this->course->title)
            ->line('**Transaction ID:** ' . $this->payment->txnid)
            ->line('**Amount Paid:** ₹' . number_format((float) $this->payment->amount, 2))
            ->line('**Date:** ' . $this->payment->created_at->format('F j, Y, g:i a'))
            ->line('You can now access your course materials from your student dashboard.')
            ->action('Go to Dashboard', url('/login'));

        if ($this->isNewUser && $this->password) {
            $mail->line('---');
            $mail->line('**Your Login Credentials**');
            $mail->line('**Email:** ' . $notifiable->email);
            $mail->line('**Password:** ' . $this->password);
            $mail->line('Please change your password after logging in for the first time.');
        }

        $mail->line('If you have any questions, feel free to contact our support team.')
            ->line('Thank you for choosing ' . config('app.name') . '!');

        return $mail;
    }
}