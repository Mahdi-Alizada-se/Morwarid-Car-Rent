<?php

namespace App\Services;

use App\Exceptions\BookingConflictException;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private VehicleAvailabilityService $availability,
        private PricingCalculator $pricing,
        private NotificationService $notifications,
    ) {
    }

    // ─── Create Booking ───────────────────────────────────────────────────────

    public function createBooking(User $customer, array $data): Booking
    {
        return DB::transaction(function () use ($customer, $data) {

            $vehicle = Vehicle::lockForUpdate()->findOrFail($data['vehicle_id']);
            $from = Carbon::parse($data['pickup_date']);
            $to = Carbon::parse($data['return_date']);

            // Check availability with row locking
            $available = $this->availability->isAvailable(
                $vehicle->id,
                $from,
                $to
            );

            if (!$available) {
                throw new BookingConflictException();
            }

            // Calculate price
            $vehicle->load(['pricingRules' => fn($q) => $q->where('is_active', true)]);
            $priceData = $this->pricing->calculate($vehicle, $from, $to);

            // Create booking
            $booking = Booking::create([
                'reference_code' => $this->generateReferenceCode(),
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'pickup_date' => $from,
                'return_date' => $to,
                'pickup_location' => $data['pickup_location'] ?? null,
                'return_location' => $data['return_location'] ?? null,
                'status' => Booking::STATUS_PENDING,
                'total_amount' => $priceData['amount'],
                'currency' => $priceData['currency'],
                'notes' => $data['notes'] ?? null,
                'booked_at' => now(),

            ]);

            // Notify customer — booking received
            $this->notifications->bookingCreated($booking);

            return $booking;
        });
    }

    // ─── Confirm Booking ──────────────────────────────────────────────────────

    public function confirmBooking(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
            ]);

            // Notify customer — payment confirmed
            $this->notifications->paymentConfirmed($booking);
        });
    }

    // ─── Start Rental ─────────────────────────────────────────────────────────

    public function startRental(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $booking->update([
                'status' => Booking::STATUS_ACTIVE,
            ]);

            $booking->vehicle->update([
                'status' => 'booked',
            ]);
        });
    }

    // ─── Complete Rental ──────────────────────────────────────────────────────

    public function completeRental(Booking $booking, ?Carbon $actualReturn = null): void
    {
        DB::transaction(function () use ($booking, $actualReturn) {
            $booking->update([
                'status' => Booking::STATUS_COMPLETED,
                'actual_return_date' => $actualReturn ?? now(),
            ]);

            $booking->vehicle->update([
                'status' => 'available',
            ]);
        });
    }

    // ─── Cancel Booking ───────────────────────────────────────────────────────

    public function cancelBooking(Booking $booking, string $reason): void
    {
        DB::transaction(function () use ($booking, $reason) {

            $hoursElapsed = now()->diffInHours(
                $booking->booked_at ?? $booking->created_at
            );

            if ($hoursElapsed > 5) {
                // ─── Late Cancellation — fee applies ──────────────────────────────
                $fee = $booking->getCancellationFeeAmount();

                $booking->cancellation_fee = $fee;
                $booking->cancellation_fee_paid = false;
                $booking->status = Booking::STATUS_CANCELLED;
                $booking->cancellation_reason = $reason;
                $booking->cancelled_at = now();
                $booking->save();

                // Free the vehicle if it was booked
                if ($booking->vehicle?->status === 'booked') {
                    $booking->vehicle->update(['status' => 'available']);
                }

                // Notify customer with fee details
                $booking->customer->notify(
                    new \App\Notifications\BookingCancelledWithFeeNotification($booking)
                );

            } else {
                // ─── Free Cancellation ────────────────────────────────────────────
                $booking->status = Booking::STATUS_CANCELLED;
                $booking->cancellation_reason = $reason;
                $booking->cancelled_at = now();
                $booking->save();

                // Free the vehicle if it was booked
                if ($booking->vehicle?->status === 'booked') {
                    $booking->vehicle->update(['status' => 'available']);
                }

                // Notify customer — free cancellation
                $booking->customer->notify(
                    new \App\Notifications\BookingCancelledNotification($booking)
                );
            }

            // Notify via our new in-app notification bell too
            $this->notifications->bookingCancelled($booking);
        });
    }

    // ─── Generate Reference Code ──────────────────────────────────────────────

    private function generateReferenceCode(): string
    {
        do {
            $code = 'CR-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Booking::where('reference_code', $code)->exists());

        return $code;
    }
}