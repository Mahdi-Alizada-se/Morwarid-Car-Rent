<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\VehicleLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class GpsController extends Controller
{
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
            return response()->json([
                'message' => "Too many requests. Try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($throttleKey, 10); // decay 10 seconds

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        // Validate vehicle has active booking
        $activeBooking = Booking::where('vehicle_id', $vehicle->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$activeBooking) {
            return response()->json([
                'message' => 'No active booking found for this vehicle.',
            ], 422);
        }

        // Create location record
        $location = VehicleLocation::create([
            'vehicle_id' => $vehicle->id,
            'booking_id' => $activeBooking->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'recorded_at' => now(),
        ]);

        // Broadcast location update
        broadcast(new VehicleLocationUpdated(
            $location,
            $vehicle->id,
            $vehicle->full_name
        ));

        return response()->json([
            'message' => 'Location updated.',
            'location' => [
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'speed' => $location->speed ? (float) $location->speed : null,
                'heading' => $location->heading ? (float) $location->heading : null,
                'recorded_at' => $location->recorded_at->toISOString(),
            ],
        ]);
    }

    // ─── Get Live Location ────────────────────────────────────────────────────

    public function liveLocation(Vehicle $vehicle): JsonResponse
    {
        $location = VehicleLocation::where('vehicle_id', $vehicle->id)
            ->latest('recorded_at')
            ->first();

        if (!$location) {
            return response()->json(['message' => 'No location data available.'], 404);
        }

        return response()->json([
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
            return response()->json(['message' => 'No active booking for this vehicle.'], 404);
        }

        $locations = VehicleLocation::where('vehicle_id', $vehicle->id)
            ->where('booking_id', $activeBooking->id)
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'speed', 'heading', 'recorded_at']);

        return response()->json([
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

        return response()->json(['vehicles' => $locations]);
    }
}