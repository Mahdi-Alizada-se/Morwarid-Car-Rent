<?php

namespace App\Services;

use App\Events\PaymentReceiptUploaded;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentConfirmedNotification;
use App\Notifications\PaymentRejectedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    public function __construct(
        private BookingService $bookingService,
        private InvoiceService $invoiceService,
    ) {
    }

    // ─── Initiate Bank Transfer ───────────────────────────────────────────────

    public function initiateBankTransfer(Booking $booking): Payment
    {
        // Cancel any existing pending payments for this booking
        $booking->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->update(['status' => Payment::STATUS_FAILED]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->customer_id,
            'method' => Payment::METHOD_BANK_TRANSFER,
            'amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'status' => Payment::STATUS_PENDING,
        ]);

        $payment->bankAccounts = BankAccount::active()->get();

        return $payment;
    }

    // ─── Upload Receipt ───────────────────────────────────────────────────────

    public function uploadReceipt(
        Payment $payment,
        UploadedFile $file,
        string $bankReference = null
    ): Payment {
        // Store the receipt file
        $extension = $file->getClientOriginalExtension();
        $filename = 'receipts/' . $payment->booking->reference_code . '.' . $extension;

        Storage::disk('public')->putFileAs(
            'receipts',
            $file,
            $payment->booking->reference_code . '.' . $extension
        );

        $payment->update([
            'receipt_path' => $filename,
            'bank_reference' => $bankReference,
            'status' => Payment::STATUS_RECEIPT_UPLOADED,
        ]);

        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\PaymentReceiptUploadedNotification($payment));
        }

        event(new PaymentReceiptUploaded($payment));

        return $payment->fresh();
    }

    // ─── Confirm Payment ──────────────────────────────────────────────────────

    public function confirmPayment(Payment $payment, User $admin): Payment
    {
        $payment->update([
            'status' => Payment::STATUS_PAID,
            'confirmed_by' => $admin->id,
            'paid_at' => now(),
        ]);

        // Confirm the booking
        $this->bookingService->confirmBooking($payment->booking);

        // Generate invoice
        $invoicePath = $this->invoiceService->generateInvoice($payment->fresh());
        $payment->update(['invoice_path' => $invoicePath]);

        // Notify customer
        $payment->booking->customer->notify(
            new PaymentConfirmedNotification($payment->fresh())
        );

        return $payment->fresh();
    }

    // ─── Reject Receipt ───────────────────────────────────────────────────────

    public function rejectReceipt(Payment $payment, string $reason): Payment
    {
        $payment->update([
            'status' => Payment::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        // Notify customer
        $payment->booking->customer->notify(
            new PaymentRejectedNotification($payment->fresh())
        );

        return $payment->fresh();
    }

    // ─── Record Counter Payment ───────────────────────────────────────────────

    public function recordCounterPayment(Booking $booking, User $admin): Payment
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $booking->customer_id,
            'method' => Payment::METHOD_COUNTER,
            'amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'status' => Payment::STATUS_PAID,
            'confirmed_by' => $admin->id,
            'paid_at' => now(),
        ]);

        // Confirm the booking
        $this->bookingService->confirmBooking($booking);

        // Generate invoice
        $invoicePath = $this->invoiceService->generateInvoice($payment->fresh());
        $payment->update(['invoice_path' => $invoicePath]);

        // Notify customer
        $booking->customer->notify(
            new PaymentConfirmedNotification($payment->fresh())
        );

        return $payment->fresh();
    }
}