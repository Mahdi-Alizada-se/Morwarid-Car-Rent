<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:send-reminders
                            {--dry-run : Show who would be notified without sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send booking reminder notifications to customers whose pickup is tomorrow';

    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $this->info("Looking for bookings with pickup date: {$tomorrow}");

        $bookings = Booking::with(['customer', 'vehicle'])
            ->whereDate('pickup_date', $tomorrow)
            ->whereIn('status', ['confirmed', 'pending'])
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings found for tomorrow. Nothing to send.');
            return self::SUCCESS;
        }

        $this->info("Found {$bookings->count()} booking(s) for tomorrow.");

        $sent = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            if (!$booking->customer) {
                $this->warn("Skipping booking {$booking->reference_code} — no customer found.");
                $skipped++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  [DRY RUN] Would notify: {$booking->customer->email} — {$booking->reference_code}");
                continue;
            }

            try {
                $booking->customer->notify(new BookingReminderNotification($booking));
                $this->line("  ✓ Sent to: {$booking->customer->email} — {$booking->reference_code}");
                $sent++;
            } catch (\Exception $e) {
                $this->error("  ✗ Failed for {$booking->reference_code}: {$e->getMessage()}");
                $skipped++;
            }
        }

        if (!$this->option('dry-run')) {
            $this->info("Done. Sent: {$sent} | Skipped: {$skipped}");
        }

        return self::SUCCESS;
    }
}