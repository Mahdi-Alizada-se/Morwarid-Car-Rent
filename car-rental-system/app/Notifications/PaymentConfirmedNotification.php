<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PaymentConfirmedNotification extends Notification implements ShouldQueue
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
        $mail = (new MailMessage)
            ->subject('Payment Confirmed — ' . $booking->reference_code)
            ->view('emails.payment-confirmed', [
                'payment' => $this->payment,
                'booking' => $booking,
                'customer' => $notifiable,
            ]);

        // Attach invoice PDF if it exists
        if ($this->payment->invoice_path && Storage::disk('public')->exists($this->payment->invoice_path)) {
            $mail->attachFromStorage(
                $this->payment->invoice_path,
                'Invoice-' . $booking->reference_code . '.pdf',
                ['disk' => 'public']
            );
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $booking = $this->payment->booking;

        return [
            'type' => 'payment_confirmed',
            'payment_id' => $this->payment->id,
            'booking_id' => $booking->id,
            'reference_code' => $booking->reference_code,
            'amount' => $this->payment->amount,
            'invoice_url' => $this->payment->invoice_path
                ? Storage::disk('public')->url($this->payment->invoice_path)
                : null,
            'message' => 'Payment confirmed for booking ' . $booking->reference_code,
        ];
    }
}