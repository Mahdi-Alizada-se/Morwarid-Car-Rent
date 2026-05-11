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
        try {
            $booking = $this->bookingService->createBooking(
                auth()->user(),
                $request->validated()
            );

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('success', __('Booking created successfully.'));

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
}