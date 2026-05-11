<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Payment $payment,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->payment->booking;

        return (new MailMessage)
            ->subject('Receipt Not Accepted — ' . $booking->reference_code)
            ->view('emails.payment-rejected', [
                'payment' => $this->payment,
                'booking' => $booking,
                'customer' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $booking = $this->payment->booking;

        return [
            'type' => 'payment_rejected',
            'payment_id' => $this->payment->id,
            'booking_id' => $booking->id,
            'reference_code' => $booking->reference_code,
            'rejection_reason' => $this->payment->rejection_reason,
            'message' => 'Receipt rejected for booking ' . $booking->reference_code,
        ];
    }
}