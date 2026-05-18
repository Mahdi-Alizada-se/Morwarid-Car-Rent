<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledWithFeeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Booking $booking,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fee = number_format($this->booking->cancellation_fee, 0);

        return (new MailMessage)
            ->subject('Booking Cancelled — Cancellation Fee of AFN ' . $fee . ' Applies')
            ->view('emails.booking-cancelled-fee', [
                'booking' => $this->booking,
                'customer' => $notifiable,
                'fee' => $fee,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_cancelled_with_fee',
            'booking_id' => $this->booking->id,
            'reference_code' => $this->booking->reference_code,
            'cancellation_fee' => $this->booking->cancellation_fee,
            'message' => 'Your booking ' . $this->booking->reference_code
                . ' was cancelled. A fee of AFN '
                . number_format($this->booking->cancellation_fee, 0)
                . ' applies.',
        ];
    }
}