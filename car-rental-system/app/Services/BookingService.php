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
                'payment_method' => $data['payment_method'] ?? 'counter',
            ]);

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
            $wasActive = $booking->status === Booking::STATUS_ACTIVE;

            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Free the vehicle only if rental was active
            if ($wasActive) {
                $booking->vehicle->update([
                    'status' => 'available',
                ]);
            }
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