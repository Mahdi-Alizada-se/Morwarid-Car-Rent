<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\BookingConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        // Block admins from making bookings
        if (auth()->user()->role === 'admin') {
            return redirect()
                ->back()
                ->with('error', 'Administrators cannot make bookings.');
        }

        try {
            $booking = $this->bookingService->createBooking(
                auth()->user(),
                $request->validated()
            );

            $paymentMethod = $request->input('payment_method', 'cash');

            if ($paymentMethod === 'bank_transfer') {

                // Bank transfer needs admin review — keep booking pending
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
                    ->with('success', 'Booking received! Your bank transfer is under review. We will confirm your booking shortly.');

            } elseif ($paymentMethod === 'mastercard') {

                // Auto-confirm booking
                $booking->update(['status' => 'confirmed']);

                $booking->payments()->create([
                    'amount' => $booking->total_amount,
                    'method' => 'online',
                    'status' => 'paid',
                    'user_id' => auth()->id(),
                    'notes' => 'Card ending in: ' . $request->input('card_last_four', '****'),
                ]);

                return redirect()
                    ->route('bookings.confirmed', $booking)
                    ->with('success', 'Booking confirmed! Your card payment was successful.');

            } else {
                // Cash — pending until admin confirms within 5 hours
                $booking->payments()->create([
                    'amount' => $booking->total_amount,
                    'method' => 'cash',
                    'status' => 'pending',
                    'user_id' => auth()->id(),
                ]);

                // Schedule auto-cancel after 5 hours if not confirmed
                \App\Jobs\AutoCancelUnconfirmedBooking::dispatch($booking)
                    ->delay(now()->addHours(5));

                return redirect()
                    ->route('bookings.confirmed', $booking)
                    ->with('success', 'Booking received! Please pay at our office within 5 hours or your booking will be automatically cancelled.');
            }

        } catch (BookingConflictException $e) {
            return back()
                ->withInput()
                ->withErrors(['vehicle_id' => $e->getMessage()]);
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
        // Customer can only see their own bookings
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
            return back()->with('error', __('This booking cannot be cancelled.'));
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->bookingService->cancelBooking(
            $booking,
            $request->reason ?? 'Cancelled by customer.'
        );

        return redirect()
            ->route('customer.bookings.index')
            ->with('success', __('Booking cancelled successfully.'));
    }


    // ─── Confirmed Page ───────────────────────────────────────────────────────────

    public function confirmed(Booking $booking): View
    {
        // Check ownership — customer can only see their own, admin can see all
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
