<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
    ) {
    }

    // ─── Create Form ──────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $vehicle = Vehicle::with([
            'category',
            'images',
            'pricingRules' => fn($q) => $q->where('is_active', true),
        ])->findOrFail($request->vehicle_id);

        return view('bookings.create', compact('vehicle'));
    }

    // ─── Store Booking ────────────────────────────────────────────────────────

    public function store(StoreBookingRequest $request): mixed
    {
        if (auth()->user()->role === 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Admins cannot book.'], 403);
            }
            return redirect()->back()->with('error', 'Administrators cannot make bookings.');
        }

        try {
            $booking = $this->bookingService->createBooking(
                auth()->user(),
                $request->validated()
            );

            $paymentMethod = $request->input('payment_method', 'cash');

            // Clear chatbot cache so AI sees the new booking immediately
            Cache::flush();

            // If Stripe — return JSON with booking ID for JS to handle
            if ($paymentMethod === 'mastercard' && $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->id,
                ]);
            }

            if ($paymentMethod === 'bank_transfer') {
                $booking->payments()->create([
                    'amount' => $booking->total_amount,
                    'method' => 'bank_transfer',
                    'status' => 'receipt_uploaded',
                    'user_id' => auth()->id(),
                    'notes' => 'Reference: ' . $request->input('bank_reference', '')
                        . ' | Sender: ' . $request->input('bank_sender_name', ''),
                ]);

                return redirect()
                    ->route('bookings.confirmed', $booking)
                    ->with('success', 'Booking received! Your bank transfer is under review.');

            } elseif ($paymentMethod === 'mastercard') {
                return redirect()
                    ->route('bookings.confirmed', $booking)
                    ->with('success', 'Booking confirmed!');

            } else {
                $booking->payments()->create([
                    'amount' => $booking->total_amount,
                    'method' => 'cash',
                    'status' => 'pending',
                    'user_id' => auth()->id(),
                ]);

                \App\Jobs\AutoCancelUnconfirmedBooking::dispatch($booking)
                    ->delay(now()->addHours(5));

                return redirect()
                    ->route('bookings.confirmed', $booking)
                    ->with('success', 'Booking received! Please pay within 5 hours.');
            }

        } catch (\App\Exceptions\BookingConflictException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 409);
            }
            return back()->withInput()->withErrors(['vehicle_id' => $e->getMessage()]);
        }
    }

    // ─── My Bookings List ─────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $bookings = Booking::with(['vehicle'])
            ->where('customer_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    // ─── Show Booking ─────────────────────────────────────────────────────────

    public function show(Booking $booking): View
    {
        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['vehicle', 'payments']);

        return view('bookings.show', compact('booking'));
    }

    // ─── Cancel Booking ───────────────────────────────────────────────────────

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->canBeCancelled()) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $fee = $booking->getCancellationFeeAmount();
        $feeDesc = $booking->getCancellationFeeDescription();

        $booking->cancel($request->reason ?? 'Cancelled by customer.');

        // Clear chatbot cache so AI sees the cancellation immediately
        Cache::flush();

        $message = $fee > 0
            ? "Booking cancelled. {$feeDesc}. Please contact us to settle the fee."
            : 'Booking cancelled successfully. No fee applied.';

        return redirect()
            ->route('customer.bookings.index')
            ->with('success', $message);
    }

    // ─── Confirmed Page ───────────────────────────────────────────────────────

    public function confirmed(Booking $booking): View
    {
        if (auth()->id() !== $booking->customer_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $booking->load([
            'customer',
            'vehicle.category',
            'vehicle.pricingRules',
            'payments',
        ]);

        $dailyRate = $booking->vehicle
            ?->pricingRules
            ->where('type', 'daily')
            ->where('is_active', true)
            ->first()
                ?->base_rate ?? 0;

        return view('bookings.confirmed', compact('booking', 'dailyRate'));
    }

    // ─── Cash Pending Page ────────────────────────────────────────────────────

    public function cashPending(Booking $booking): View
    {
        if ($booking->customer_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['vehicle', 'payments']);

        return view('bookings.cash-pending', compact('booking'));
    }
}