<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your Driver's License Has Been Verified — Morwarid Car Rental")
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line("Great news! Your driver's license has been verified by our team.")
            ->line('You can now book vehicles on Morwarid Car Rental.')
            ->action('Browse Vehicles', url('/vehicles'))
            ->line('Thank you for choosing ' . config('company.name') . '!')
            ->line('For questions contact us at: ' . config('company.phone'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'license_verified',
            'message' => "Your driver's license has been verified. You can now book vehicles!",
        ];
    }
}