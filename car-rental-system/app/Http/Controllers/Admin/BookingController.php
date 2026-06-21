<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
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

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Booking::with(['customer', 'vehicle'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('pickup_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('pickup_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_code', 'like', '%' . $request->search . '%')
                    ->orWhereHas(
                        'customer',
                        fn($cq) =>
                        $cq->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%')
                    );
            });
        }

        $bookings = $query->paginate(15)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Booking $booking): View
    {
        $booking->load(['customer', 'vehicle', 'payments']);

        return view('admin.bookings.show', compact('booking'));
    }

    // ─── Update Status ────────────────────────────────────────────────────────

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
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

        // Clear chatbot cache so AI gets fresh booking data
        Cache::flush();

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking status updated successfully.');
    }

    // ─── Mark Cancellation Fee As Paid ───────────────────────────────────────

    public function markFeePaid(Booking $booking): RedirectResponse
    {
        $booking->update(['cancellation_fee_paid' => true]);

        // Clear chatbot cache
        Cache::flush();

        return back()->with('success', 'Cancellation fee marked as paid.');
    }

    // ─── Calendar Data (JSON for FullCalendar) ────────────────────────────────

    public function calendarData(Request $request): JsonResponse
    {
        $bookings = Booking::with(['vehicle', 'customer'])
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->when(
                $request->filled('start'),
                fn($q) =>
                $q->whereDate('pickup_date', '>=', $request->start)
            )
            ->when(
                $request->filled('end'),
                fn($q) =>
                $q->whereDate('return_date', '<=', $request->end)
            )
            ->get();

        $events = $bookings->map(fn(Booking $booking) => [
            'id' => $booking->id,
            'title' => $booking->reference_code . ' — ' . $booking->vehicle?->full_name,
            'start' => $booking->pickup_date->toDateString(),
            'end' => $booking->return_date->toDateString(),
            'color' => match ($booking->status) {
                'pending' => '#f59e0b',
                'confirmed' => '#3b82f6',
                'active' => '#10b981',
                'completed' => '#6b7280',
                default => '#ef4444',
            },
            'extendedProps' => [
                'customer' => $booking->customer?->name,
                'status' => $booking->status,
                'amount' => 'AFN ' . number_format($booking->total_amount),
            ],
        ]);

        return response()->json($events);
    }
}