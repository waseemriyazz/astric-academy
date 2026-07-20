<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseEnrolled extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public Course $course
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $appName = config('app.name');
        $loginUrl = $frontendUrl . '/login?email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Welcome back to ' . $appName . ' — New Enrollment Confirmed!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! You have successfully enrolled in a new course on ' . $appName . '.')
            ->line('---')
            ->line('**Course:** ' . $this->course->title)
            ->line('**Transaction ID:** ' . $this->payment->txnid)
            ->line('**Amount Paid:** ₹' . number_format((float) $this->payment->amount, 2))
            ->line('**Date:** ' . $this->payment->created_at->format('F j, Y, g:i a'))
            ->line('---')
            ->line('You can now access your new course from your student dashboard.')
            ->action('Go to Your Dashboard', $loginUrl)
            ->line('If you have any questions, feel free to contact our support team at support@astryxacademy.com.')
            ->line('---')
            ->line('Best regards,')
            ->line('The ' . $appName . ' Team');
    }
}