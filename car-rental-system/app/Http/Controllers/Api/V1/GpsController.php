<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\VehicleLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class GpsController extends Controller
{
    use ApiResponseTrait;

    // ─── Update Vehicle Location ──────────────────────────────────────────────

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
        ]);

        // Throttle: 1 request per 10 seconds per vehicle
        $throttleKey = 'gps-update:' . $request->vehicle_id;

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return $this->errorResponse(
                "Too many requests. Try again in {$seconds} seconds.",
                429
            );
        }

        RateLimiter::hit($throttleKey, 10);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        // Find active or confirmed booking
        $booking = Booking::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['active', 'confirmed'])
            ->latest()
            ->first();

        // Create location record
        VehicleLocation::create([
            'vehicle_id' => $vehicle->id,
            'booking_id' => $booking?->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed ?? 0,
            'heading' => $request->heading ?? 0,
            'recorded_at' => now(),
        ]);

        // Update vehicle's cached location for display on vehicle cards
        $vehicle->update([
            'last_seen_at' => now(),
            'last_latitude' => $request->latitude,
            'last_longitude' => $request->longitude,
            'last_speed' => $request->speed ?? 0,
        ]);

        // Broadcast location update
        broadcast(new VehicleLocationUpdated(
            vehicleId: $vehicle->id,
            vehicleName: $vehicle->brand . ' ' . $vehicle->model,
            latitude: (float) $request->latitude,
            longitude: (float) $request->longitude,
            speed: (float) ($request->speed ?? 0),
            heading: (float) ($request->heading ?? 0),
        ));

        return $this->successResponse(
            ['recorded_at' => now()->toISOString()],
            'Location updated'
        );
    }

    // ─── Get Live Location ────────────────────────────────────────────────────

    public function liveLocation(Vehicle $vehicle): JsonResponse
    {
        $location = VehicleLocation::where('vehicle_id', $vehicle->id)
            ->latest('recorded_at')
            ->first();

        if (!$location) {
            return $this->notFoundResponse('Location data');
        }

        return $this->successResponse([
            'vehicle' => [
                'id' => $vehicle->id,
                'full_name' => $vehicle->full_name,
                'status' => $vehicle->status,
            ],
            'location' => [
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'speed' => $location->speed ? (float) $location->speed : null,
                'heading' => $location->heading ? (float) $location->heading : null,
                'recorded_at' => $location->recorded_at->toISOString(),
                'recorded_ago' => $location->recorded_at->diffForHumans(),
            ],
        ]);
    }

    // ─── Location History ─────────────────────────────────────────────────────

    public function history(Vehicle $vehicle): JsonResponse
    {
        $activeBooking = Booking::where('vehicle_id', $vehicle->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$activeBooking) {
            return $this->notFoundResponse('Active booking');
        }

        $locations = VehicleLocation::where('vehicle_id', $vehicle->id)
            ->where('booking_id', $activeBooking->id)
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'speed', 'heading', 'recorded_at']);

        return $this->successResponse([
            'vehicle' => $vehicle->full_name,
            'booking' => $activeBooking->reference_code,
            'locations' => $locations->map(fn($l) => [
                'latitude' => (float) $l->latitude,
                'longitude' => (float) $l->longitude,
                'speed' => $l->speed ? (float) $l->speed : null,
                'heading' => $l->heading ? (float) $l->heading : null,
                'recorded_at' => $l->recorded_at->toISOString(),
            ]),
        ]);
    }

    // ─── Admin: All Active Vehicle Locations ──────────────────────────────────

    public function activeLocations(): JsonResponse
    {
        $activeBookings = Booking::with(['vehicle', 'customer'])
            ->where('status', 'active')
            ->get();

        $locations = $activeBookings->map(function (Booking $booking) {
            $latestLocation = VehicleLocation::where('vehicle_id', $booking->vehicle_id)
                ->latest('recorded_at')
                ->first();

            if (!$latestLocation) {
                return null;
            }

            return [
                'vehicle_id' => $booking->vehicle_id,
                'vehicle_name' => $booking->vehicle?->full_name,
                'customer' => $booking->customer?->name,
                'reference' => $booking->reference_code,
                'latitude' => (float) $latestLocation->latitude,
                'longitude' => (float) $latestLocation->longitude,
                'speed' => $latestLocation->speed
                    ? (float) $latestLocation->speed
                    : null,
                'heading' => $latestLocation->heading
                    ? (float) $latestLocation->heading
                    : null,
                'recorded_at' => $latestLocation->recorded_at->toISOString(),
                'recorded_ago' => $latestLocation->recorded_at->diffForHumans(),
            ];
        })->filter()->values();

        return $this->successResponse(['vehicles' => $locations]);
    }
}