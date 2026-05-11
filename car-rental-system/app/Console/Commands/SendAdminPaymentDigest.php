<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAdminPaymentDigest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payments:send-digest
                            {--dry-run : Show digest info without sending email}';

    /**
     * The console command description.
     */
    protected $description = 'Send admin a daily digest of payments awaiting review';

    public function handle(): int
    {
        $pendingPayments = Payment::with(['booking.customer', 'booking.vehicle'])
            ->where('status', Payment::STATUS_RECEIPT_UPLOADED)
            ->oldest()
            ->get();

        $this->info("Found {$pendingPayments->count()} payment(s) awaiting review.");

        if ($pendingPayments->isEmpty()) {
            $this->info('No payments need review today. No digest sent.');
            return self::SUCCESS;
        }

        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Cannot send digest.');
            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->line('');
            $this->line('[DRY RUN] Digest would be sent to:');
            foreach ($admins as $admin) {
                $this->line("  - {$admin->email}");
            }
            $this->line('');
            $this->line('Payments awaiting review:');
            foreach ($pendingPayments as $payment) {
                $this->line("  - {$payment->booking?->reference_code} | {$payment->booking?->customer?->name} | AFN " . number_format($payment->amount));
            }
            return self::SUCCESS;
        }

        foreach ($admins as $admin) {
            try {
                Mail::send(
                    'emails.admin-payment-digest',
                    [
                        'admin' => $admin,
                        'payments' => $pendingPayments,
                        'count' => $pendingPayments->count(),
                        'totalAmount' => $pendingPayments->sum('amount'),
                        'generatedAt' => Carbon::now()->format('M d, Y — H:i'),
                    ],
                    function ($message) use ($admin) {
                        $message->to($admin->email, $admin->name)
                            ->subject('Payment Review Digest — ' . $pendingPayments->count() . ' Awaiting — ' . now()->format('M d, Y'));
                    }
                );

                $this->line("  ✓ Digest sent to: {$admin->email}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send to {$admin->email}: {$e->getMessage()}");
            }
        }

        $this->info('Digest sent successfully.');

        return self::SUCCESS;
    }
}