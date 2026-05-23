<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private InvoiceService $invoiceService,
    ) {
    }

    // ─── Checkout Page ────────────────────────────────────────────────────────

    public function checkout(Booking $booking): View
    {
        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        $bankAccounts = BankAccount::active()->get();

        $payment = $booking->payments()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_RECEIPT_UPLOADED])
            ->latest()
            ->first();

        return view('payments.checkout', compact('booking', 'bankAccounts', 'payment'));
    }

    // ─── Payment Status Page ──────────────────────────────────────────────────

    public function status(Payment $payment): View
    {
        if ($payment->booking->customer_id !== auth()->id()) {
            abort(403);
        }

        $payment->load(['booking.vehicle']);

        return view('payments.status', compact('payment'));
    }

    // ─── Counter Payment ──────────────────────────────────────────────────────

    public function counter(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'method' => Payment::METHOD_COUNTER,
            'amount' => $booking->total_amount,
            'currency' => $booking->currency ?? 'AFN',
            'status' => Payment::STATUS_PENDING,
        ]);

        return redirect()
            ->route('customer.payments.status', $payment)
            ->with('success', __('payments.counter_selected'));
    }

    // ─── Bank Transfer ────────────────────────────────────────────────────────

    public function initiateBankTransfer(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'method' => Payment::METHOD_BANK_TRANSFER,
            'amount' => $booking->total_amount,
            'currency' => $booking->currency ?? 'AFN',
            'status' => Payment::STATUS_PENDING,
        ]);

        return redirect()
            ->route('customer.payments.status', $payment)
            ->with('success', __('payments.bank_transfer_initiated'));
    }

    // ─── Cash Payment ─────────────────────────────────────────────────────────

    public function cashPayment(Booking $booking): RedirectResponse
    {
        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        // Prevent duplicate payments
        $existing = $booking->payments()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_PAID])
            ->first();

        if ($existing) {
            return redirect()
                ->route('bookings.cash-pending', $booking)
                ->with('info', 'You already have a pending payment for this booking.');
        }

        Payment::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'method' => Payment::METHOD_CASH,
            'amount' => $booking->total_amount,
            'currency' => $booking->currency ?? 'AFN',
            'status' => Payment::STATUS_PENDING,
        ]);

        $booking->update(['status' => 'pending']);

        // Notify customer about 5-hour window
        try {
            $booking->customer->notify(
                new \App\Notifications\CashPaymentPendingNotification($booking)
            );
        } catch (\Exception $e) {
            // Don't fail if notification fails
        }

        return redirect()
            ->route('bookings.cash-pending', $booking)
            ->with('success', 'Booking reserved! Please visit our office within 5 hours.');
    }

    // ─── Online Payment Confirm ───────────────────────────────────────────────

    public function onlineConfirm(Booking $booking): RedirectResponse
    {
        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        Payment::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'method' => Payment::METHOD_ONLINE,
            'amount' => $booking->total_amount,
            'currency' => $booking->currency ?? 'AFN',
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
            'transaction_id' => 'ONL-' . strtoupper(uniqid()),
        ]);

        $booking->update(['status' => 'confirmed']);
        $booking->vehicle?->update(['status' => 'booked']);

        try {
            $booking->customer->notify(new BookingConfirmedNotification($booking));
        } catch (\Exception $e) {
        }

        try {
            $this->invoiceService->generate($booking);
        } catch (\Exception $e) {
        }

        return redirect()
            ->route('bookings.confirmed', $booking)
            ->with('success', 'Payment successful! Your booking is confirmed.');
    }
}