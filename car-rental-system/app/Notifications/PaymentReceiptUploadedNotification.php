<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceiptUploadedNotification extends Notification implements ShouldQueue
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
            ->subject('Receipt Uploaded — ' . $booking->reference_code)
            ->view('emails.receipt-uploaded', [
                'payment' => $this->payment,
                'booking' => $booking,
                'admin' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $booking = $this->payment->booking;

        return [
            'type' => 'receipt_uploaded',
            'payment_id' => $this->payment->id,
            'booking_id' => $booking->id,
            'reference_code' => $booking->reference_code,
            'customer_name' => $booking->customer?->name,
            'amount' => $this->payment->amount,
            'message' => 'Receipt uploaded for booking ' . $booking->reference_code,
        ];
    }
}