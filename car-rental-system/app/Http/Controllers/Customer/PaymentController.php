<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {
    }

    public function checkout(Booking $booking): View
    {
        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        $bankAccounts = BankAccount::active()->get();

        // Get or create pending payment
        $payment = $booking->payments()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_RECEIPT_UPLOADED])
            ->latest()
            ->first();

        return view('payments.checkout', compact('booking', 'bankAccounts', 'payment'));
    }

    public function status(Payment $payment): View
    {
        if ($payment->booking->customer_id !== auth()->id()) {
            abort(403);
        }

        $payment->load(['booking.vehicle']);

        return view('payments.status', compact('payment'));
    }

    public function counter(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        // Initiate counter payment — admin will confirm at pickup
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'method' => Payment::METHOD_COUNTER,
            'amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'status' => Payment::STATUS_PENDING,
        ]);

        return redirect()
            ->route('payments.status', $payment)
            ->with('success', __('payments.counter_selected'));
    }
}