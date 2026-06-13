<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // ─── Create Payment Intent ────────────────────────────────────────────────

    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->customer_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Convert AFN to USD cents (1 USD ≈ 72 AFN)
        $amountInCents = intval(($booking->total_amount / 72) * 100);
        $amountInCents = max($amountInCents, 50);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'reference_code' => $booking->reference_code,
                    'customer_id' => auth()->id(),
                ],
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $booking->total_amount,
                'amount_usd' => number_format($amountInCents / 100, 2),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Could not create payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Confirm Payment ──────────────────────────────────────────────────────

    public function confirmPayment(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_intent_id' => ['required', 'string'],
        ]);

        try {
            $booking = Booking::findOrFail($request->booking_id);

            if ($booking->customer_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed. Please try again.',
                ], 400);
            }

            // Get actual card brand
            $cardBrand = 'card';
            try {
                if ($paymentIntent->payment_method) {
                    $paymentMethod = PaymentMethod::retrieve(
                        $paymentIntent->payment_method
                    );
                    $cardBrand = $paymentMethod->card->brand ?? 'card';
                }
            } catch (\Exception $e) {
                $cardBrand = 'card';
            }

            // Confirm booking
            $booking->update(['status' => 'confirmed']);

            // Create payment record
            $booking->payments()->create([
                'amount' => $booking->total_amount,
                'method' => strtolower($cardBrand),
                'status' => 'paid',
                'user_id' => auth()->id(),
                'booking_id' => $booking->id,
                'notes' => 'Stripe Payment ID: ' . $request->payment_intent_id
                    . ' | Card: ' . strtoupper($cardBrand),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your booking is confirmed.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment confirmation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}