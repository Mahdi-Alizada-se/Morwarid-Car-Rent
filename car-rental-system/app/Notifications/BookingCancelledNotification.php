<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification implements ShouldQueue
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
        $booking = $this->booking;

        return (new MailMessage)
            ->subject('Booking Cancelled — ' . $booking->reference_code)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Your booking **' . $booking->reference_code . '** for **' . $booking->vehicle?->full_name . '** has been successfully cancelled.')
            ->line('Since you cancelled within 5 hours of booking, **no cancellation fee applies**.')
            ->line('We hope to see you again soon!')
            ->action('Browse Vehicles', url('/vehicles'))
            ->line('For questions contact us at: ' . config('company.phone'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_cancelled',
            'booking_id' => $this->booking->id,
            'reference_code' => $this->booking->reference_code,
            'message' => 'Your booking ' . $this->booking->reference_code
                . ' has been cancelled. No fee applies.',
        ];
    }
}




