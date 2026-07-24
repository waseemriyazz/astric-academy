<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CourseEnrolled extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 30;

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
        $portalUrl = config('app.portal_url', 'http://localhost:8000');
        $appName = config('app.name');
        $loginUrl = $portalUrl . '/login?email=' . urlencode($notifiable->email);

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

    public function failed(\Throwable $exception): void
    {
        Log::error('PAYMENT FULFILL: Email permanently failed after retries', [
            'payment_id' => $this->payment->id,
            'txnid' => $this->payment->txnid,
            'error' => $exception->getMessage(),
        ]);

        try {
            Mail::raw(
                "Receipt email permanently failed after {$this->tries} attempts.\n\n"
                . "Payment ID: {$this->payment->id}\n"
                . "Transaction ID: {$this->payment->txnid}\n"
                . "Error: {$exception->getMessage()}",
                function ($message) {
                    $message->to(env('ADMIN_EMAIL', config('mail.from.address')))
                        ->subject("Receipt email failed — payment #{$this->payment->id} / txnid {$this->payment->txnid}");
                }
            );
        } catch (\Exception $e) {
            Log::error('PAYMENT FULFILL: Admin alert email also failed', [
                'payment_id' => $this->payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}