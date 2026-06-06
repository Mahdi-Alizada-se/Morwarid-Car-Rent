<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoCancelUnconfirmedBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
    ) {
    }

    public function handle(): void
    {
        // Reload fresh from database
        $booking = Booking::find($this->booking->id);

        if (!$booking)
            return;

        // Only cancel if still pending — if admin already confirmed skip
        if ($booking->status !== 'pending')
            return;

        // Cancel the booking
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => 'Automatically cancelled — cash payment not confirmed within 5 hours.',
            'cancelled_at' => now(),
        ]);

        // Update payment status
        $booking->payments()
            ->where('status', 'pending')
            ->update(['status' => 'failed']);

        \Log::info('Auto-cancelled booking #' . $booking->id . ' due to no cash confirmation.');
    }
}