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

class CoursePurchased extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 30;

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
        $symbol = $this->payment->currencySymbol();

        $mail = (new MailMessage)
            ->subject('Welcome to ' . $appName . ' — Your Enrollment is Confirmed!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for choosing ' . $appName . '. Your enrollment has been successfully confirmed.')
            ->line('---')
            ->line('**Course:** ' . $this->course->title)
            ->line('**Transaction ID:** ' . $this->payment->txnid)
            ->line('**Amount Paid:** ' . $symbol . number_format((float) $this->payment->amount, 2))
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
            ->line('If you have any questions, feel free to contact our support team at support@skillstryx.com.')
            ->line('---')
            ->line('Best regards,')
            ->line('The ' . $appName . ' Team');

        return $mail;
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