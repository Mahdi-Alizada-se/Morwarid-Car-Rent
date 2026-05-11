<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Reminder: Your Pickup is Tomorrow — ' . $booking->reference_code)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a friendly reminder that your vehicle pickup is tomorrow.')
            ->line('**Booking Reference:** ' . $booking->reference_code)
            ->line('**Vehicle:** ' . $booking->vehicle?->full_name)
            ->line('**Pickup Date:** ' . $booking->pickup_date->format('M d, Y — H:i'))
            ->line('**Return Date:** ' . $booking->return_date->format('M d, Y — H:i'))
            ->when(
                $booking->pickup_location,
                fn($mail) =>
                $mail->line('**Pickup Location:** ' . $booking->pickup_location)
            )
            ->line('**Total Amount:** AFN ' . number_format($booking->total_amount))
            ->action('View Booking', url('/my-bookings'))
            ->line('Please make sure you have a valid ID with you.')
            ->line('Thank you for choosing ' . config('app.name') . '!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_reminder',
            'booking_id' => $this->booking->id,
            'reference_code' => $this->booking->reference_code,
            'pickup_date' => $this->booking->pickup_date->toDateString(),
            'vehicle' => $this->booking->vehicle?->full_name,
            'message' => 'Your pickup is tomorrow: ' . $this->booking->reference_code,
        ];
    }
}