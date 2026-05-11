<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VehicleAvailabilityService
{
    /**
     * Check if a vehicle is available for a given date range.
     * Uses lockForUpdate() to prevent race conditions.
     */
    public function isAvailable(
        int $vehicleId,
        Carbon $from,
        Carbon $to,
        ?int $excludeBookingId = null
    ): bool {
        return DB::transaction(function () use ($vehicleId, $from, $to, $excludeBookingId) {
            // Lock the vehicle row to prevent concurrent bookings
            $vehicle = Vehicle::lockForUpdate()->find($vehicleId);

            if (!$vehicle || $vehicle->status === 'maintenance') {
                return false;
            }

            // Check for overlapping bookings
            $conflictQuery = Booking::where('vehicle_id', $vehicleId)
                ->whereNotIn('status', [Booking::STATUS_CANCELLED])
                ->where(function ($q) use ($from, $to) {
                    // Overlap condition: existing booking overlaps with requested range
                    $q->where('pickup_date', '<', $to)
                        ->where('return_date', '>', $from);
                });

            if ($excludeBookingId) {
                $conflictQuery->where('id', '!=', $excludeBookingId);
            }

            return $conflictQuery->count() === 0;
        });
    }

    /**
     * Get all available vehicles for a date range with optional filters.
     */
    public function getAvailableVehicles(Carbon $from, Carbon $to, array $filters = []): Collection
    {
        // Get IDs of vehicles that have conflicting bookings
        $bookedVehicleIds = Booking::whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->where('pickup_date', '<', $to)
            ->where('return_date', '>', $from)
            ->pluck('vehicle_id')
            ->unique();

        $query = Vehicle::with([
            'category',
            'images',
            'pricingRules' => function ($q) {
                $q->where('is_active', true)->where('type', 'daily');
            }
        ])
            ->whereNotIn('id', $bookedVehicleIds)
            ->where('status', '!=', 'maintenance')
            ->whereNull('deleted_at');

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['transmission'])) {
            $query->where('transmission', $filters['transmission']);
        }

        if (!empty($filters['fuel_type'])) {
            $query->where('fuel_type', $filters['fuel_type']);
        }

        if (!empty($filters['seats'])) {
            $query->where('seats', '>=', $filters['seats']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('brand', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('model', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $query->whereHas('pricingRules', function ($q) use ($filters) {
                $q->where('is_active', true)->where('type', 'daily');
                if (!empty($filters['min_price'])) {
                    $q->where('base_rate', '>=', $filters['min_price']);
                }
                if (!empty($filters['max_price'])) {
                    $q->where('base_rate', '<=', $filters['max_price']);
                }
            });
        }

        return $query->get();
    }

    /**
     * Get booked date ranges for a vehicle for the next 90 days.
     *
     * @return array<int, array{from: string, to: string, status: string}>
     */
    public function getVehicleCalendar(Vehicle $vehicle): array
    {
        $today = Carbon::today();
        $end = Carbon::today()->addDays(90);

        $bookings = Booking::where('vehicle_id', $vehicle->id)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->where('return_date', '>=', $today)
            ->where('pickup_date', '<=', $end)
            ->orderBy('pickup_date')
            ->get(['pickup_date', 'return_date', 'status']);

        return $bookings->map(function (Booking $booking) {
            return [
                'from' => $booking->pickup_date->toDateString(),
                'to' => $booking->return_date->toDateString(),
                'status' => $booking->status,
            ];
        })->toArray();
    }

    /**
     * Get all booked dates as a flat array of date strings (for calendar highlight).
     *
     * @return string[]
     */
    public function getBookedDates(Vehicle $vehicle): array
    {
        $ranges = $this->getVehicleCalendar($vehicle);
        $dates = [];

        foreach ($ranges as $range) {
            $current = Carbon::parse($range['from']);
            $end = Carbon::parse($range['to']);

            while ($current->lte($end)) {
                $dates[] = $current->toDateString();
                $current->addDay();
            }
        }

        return array_unique($dates);
    }
}