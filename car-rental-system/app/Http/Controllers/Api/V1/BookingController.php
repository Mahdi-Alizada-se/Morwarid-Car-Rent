<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BookingConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
    ) {
    }

    // ─── Customer: List Own Bookings ──────────────────────────────────────────

    public function index(Request $request): BookingCollection
    {
        $query = Booking::with(['vehicle', 'latestPayment'])
            ->where('customer_id', auth()->id())
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return new BookingCollection($query->paginate(10));
    }

    // ─── Customer: Create Booking ─────────────────────────────────────────────

    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking(
                auth()->user(),
                $request->validated()
            );

            $booking->load(['vehicle', 'customer']);

            return response()->json([
                'message' => 'Booking created successfully.',
                'booking' => new BookingResource($booking),
            ], 201);

        } catch (BookingConflictException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    // ─── Customer/Admin: Show Booking ─────────────────────────────────────────

    public function show(Booking $booking): JsonResponse
    {
        $user = auth()->user();

        // Customer can only see their own bookings
        if ($user->isCustomer() && $booking->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $booking->load(['vehicle', 'customer', 'latestPayment']);

        return response()->json([
            'booking' => new BookingResource($booking),
        ]);
    }

    // ─── Customer: Cancel Booking ─────────────────────────────────────────────

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $user = auth()->user();

        // Customer can only cancel their own bookings
        if ($user->isCustomer() && $booking->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (!$booking->canBeCancelled()) {
            return response()->json([
                'message' => 'This booking cannot be cancelled.',
            ], 422);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->bookingService->cancelBooking(
            $booking,
            $request->reason ?? 'Cancelled by customer.'
        );

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'booking' => new BookingResource($booking->fresh()),
        ]);
    }

    // ─── Admin: Update Status ─────────────────────────────────────────────────

    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,active,completed,cancelled'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        match ($request->status) {
            'confirmed' => $this->bookingService->confirmBooking($booking),
            'active' => $this->bookingService->startRental($booking),
            'completed' => $this->bookingService->completeRental($booking),
            'cancelled' => $this->bookingService->cancelBooking(
                $booking,
                $request->reason ?? 'Cancelled by admin.'
            ),
            default => $booking->update(['status' => $request->status]),
        };

        return response()->json([
            'message' => 'Booking status updated.',
            'booking' => new BookingResource($booking->fresh()->load(['vehicle', 'customer'])),
        ]);
    }
}