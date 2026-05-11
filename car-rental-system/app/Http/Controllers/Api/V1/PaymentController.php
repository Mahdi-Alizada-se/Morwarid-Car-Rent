<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {
    }

    // ─── Initiate Bank Transfer ───────────────────────────────────────────────

    public function initiateBankTransfer(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        // Only the booking owner can initiate payment
        if ($booking->customer_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $payment = $this->paymentService->initiateBankTransfer($booking);
        $bankAccounts = BankAccount::active()->get();

        return response()->json([
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'method' => $payment->method,
            ],
            'bank_accounts' => $bankAccounts,
        ], 201);
    }

    // ─── Upload Receipt ───────────────────────────────────────────────────────

    public function uploadReceipt(Request $request, Payment $payment): JsonResponse
    {
        // Only the booking owner can upload
        if ($payment->booking->customer_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'bank_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $payment = $this->paymentService->uploadReceipt(
            $payment,
            $request->file('receipt'),
            $request->bank_reference
        );

        return response()->json([
            'message' => 'Receipt uploaded successfully. We will confirm within 2-4 hours.',
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,
                'bank_reference' => $payment->bank_reference,
                'receipt_url' => $payment->receipt_path
                    ? Storage::disk('public')->url($payment->receipt_path)
                    : null,
            ],
        ]);
    }

    // ─── Get Payment Status ───────────────────────────────────────────────────

    public function show(Payment $payment): JsonResponse
    {
        $user = auth()->user();

        if ($user->isCustomer() && $payment->booking->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $payment->load(['booking.vehicle', 'confirmedByUser']);

        return response()->json([
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,
                'method' => $payment->method,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'bank_reference' => $payment->bank_reference,
                'rejection_reason' => $payment->rejection_reason,
                'receipt_url' => $payment->receipt_path
                    ? Storage::disk('public')->url($payment->receipt_path)
                    : null,
                'invoice_url' => $payment->invoice_path
                    ? Storage::disk('public')->url($payment->invoice_path)
                    : null,
                'paid_at' => $payment->paid_at?->toISOString(),
                'confirmed_by' => $payment->confirmedByUser?->name,
            ],
        ]);
    }

    // ─── Get Invoice ──────────────────────────────────────────────────────────

    public function invoice(Payment $payment): JsonResponse
    {
        $user = auth()->user();

        if ($user->isCustomer() && $payment->booking->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($payment->status !== Payment::STATUS_PAID) {
            return response()->json(['message' => 'Invoice is only available for paid payments.'], 422);
        }

        if (!$payment->invoice_path) {
            return response()->json(['message' => 'Invoice not yet generated.'], 404);
        }

        return response()->json([
            'invoice_url' => Storage::disk('public')->url($payment->invoice_path),
        ]);
    }
}