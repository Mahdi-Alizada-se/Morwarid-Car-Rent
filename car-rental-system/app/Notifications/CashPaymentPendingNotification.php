<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CashPaymentPendingNotification extends Notification implements ShouldQueue
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
        $deadline = $this->booking->created_at->addHours(5)->format('h:i A');

        return (new MailMessage)
            ->subject('Booking Reserved — Visit Office by ' . $deadline)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Your booking has been reserved successfully.')
            ->line('**Booking Reference:** ' . $this->booking->reference_code)
            ->line('**Amount to Pay:** AFN ' . number_format($this->booking->total_amount))
            ->line('**⚠️ Important:** You must visit our office and pay cash by **' . $deadline . '**')
            ->line('If you do not pay within 5 hours, your booking will be automatically cancelled.')
            ->line('**Office:** Morwarid Car Hub, Dasht-e-Barchi, Kabul, Afghanistan')
            ->line('**Working Hours:** 8:00 AM – 8:00 PM')
            ->action('View Booking', route('bookings.cash-pending', $this->booking))
            ->line('Thank you for choosing ' . config('company.name') . '!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cash_payment_pending',
            'message' => 'Visit our office within 5 hours to pay for booking ' . $this->booking->reference_code,
            'booking_id' => $this->booking->id,
        ];
    }
}