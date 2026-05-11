<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Payment::with(['booking.customer', 'booking.vehicle'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        $payments = $query->paginate(15)->withQueryString();

        $needsReviewCount = Payment::where('status', Payment::STATUS_RECEIPT_UPLOADED)->count();

        return view('admin.payments.index', compact('payments', 'needsReviewCount'));
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Payment $payment): View
    {
        $payment->load(['booking.customer', 'booking.vehicle', 'confirmedByUser']);

        $receiptUrl = $payment->receipt_path
            ? Storage::disk('public')->url($payment->receipt_path)
            : null;

        $invoiceUrl = $payment->invoice_path
            ? Storage::disk('public')->url($payment->invoice_path)
            : null;

        return view('admin.payments.show', compact('payment', 'receiptUrl', 'invoiceUrl'));
    }

    // ─── Confirm Payment ──────────────────────────────────────────────────────

    public function confirm(Payment $payment): RedirectResponse
    {
        if ($payment->status === Payment::STATUS_PAID) {
            return back()->with('error', 'This payment is already confirmed.');
        }

        $this->paymentService->confirmPayment($payment, auth()->user());

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment confirmed and booking activated.');
    }

    // ─── Reject Receipt ───────────────────────────────────────────────────────

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->paymentService->rejectReceipt($payment, $request->reason);

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Receipt rejected and customer notified.');
    }

    // ─── Counter Payment ──────────────────────────────────────────────────────

    public function counterPayment(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        $payment = $this->paymentService->recordCounterPayment($booking, auth()->user());

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Counter payment recorded and booking confirmed.');
    }
}