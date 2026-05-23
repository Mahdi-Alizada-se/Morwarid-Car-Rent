<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use App\Models\Payment;

// ─── Default Inspire Command ──────────────────────────────────────────────────

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Scheduled Tasks ──────────────────────────────────────────────────────────

// 1. Daily at 10:00 AM — Send booking reminders for tomorrow's pickups
Schedule::command('bookings:send-reminders')
    ->dailyAt('10:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/booking-reminders.log'));

// 2. Daily at 9:00 AM — Send admin digest of payments awaiting review
Schedule::command('payments:send-digest')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/payment-digest.log'));

// 3. Every hour — Delete receipt files from rejected payments older than 90 days
Schedule::call(function () {
    $cutoff = now()->subDays(90);

    $oldRejectedPayments = Payment::where('status', Payment::STATUS_REJECTED)
        ->where('updated_at', '<', $cutoff)
        ->whereNotNull('receipt_path')
        ->get();

    $deleted = 0;

    foreach ($oldRejectedPayments as $payment) {
        if (Storage::disk('public')->exists($payment->receipt_path)) {
            Storage::disk('public')->delete($payment->receipt_path);
            $payment->update(['receipt_path' => null]);
            $deleted++;
        }
    }

    \Illuminate\Support\Facades\Log::info("Cleaned up {$deleted} old receipt file(s).");
})->hourly()->name('cleanup-old-receipts')->withoutOverlapping();


// use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $expiredBookings = \App\Models\Booking::where('status', 'pending')
        ->whereHas(
            'payments',
            fn($q) =>
            $q->where('method', \App\Models\Payment::METHOD_CASH)
                ->where('status', \App\Models\Payment::STATUS_PENDING)
        )
        ->where('created_at', '<', now()->subHours(5))
        ->get();

    foreach ($expiredBookings as $booking) {
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => 'Customer did not visit office within 5 hours',
            'cancelled_at' => now(),
        ]);

        $booking->vehicle?->update(['status' => 'available']);

        try {
            $booking->customer->notify(
                new \App\Notifications\BookingCancelledNotification($booking)
            );
        } catch (\Exception $e) {
        }
    }
})->everyFiveMinutes()->name('cancel-expired-cash-bookings');