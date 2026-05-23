<?php

namespace App\Http\Controllers\Webhooks;

use App\Events\VehicleLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TraccarWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        // Log incoming request for debugging
        \Log::info('Traccar webhook received', $request->all());

        // Verify webhook secret
        $secret = $request->header('X-Traccar-Secret')
            ?? $request->query('secret');

        if (
            config('traccar.webhook_secret') &&
            $secret !== config('traccar.webhook_secret')
        ) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ─── Try to extract data from multiple possible formats ───────────────

        $deviceId = null;
        $latitude = null;
        $longitude = null;
        $speed = 0;
        $heading = 0;
        $accuracy = 0;
        $address = '';
        $fixTime = null;

        // Format 1: Standard Traccar event format
        // { "deviceId": "KBL-912", "latitude": 34.5, "longitude": 69.1 }
        if ($request->has('deviceId')) {
            $deviceId = $request->input('deviceId');
            $latitude = (float) $request->input('latitude', 0);
            $longitude = (float) $request->input('longitude', 0);
            $speed = (float) $request->input('speed', 0);
            $heading = (float) $request->input('course', 0);
            $accuracy = (float) $request->input('accuracy', 0);
            $address = $request->input('address', '');
            $fixTime = $request->input('fixTime');
        }

        // Format 2: Traccar event wrapper format
        // { "event": {...}, "device": {"uniqueId": "KBL-912"}, "position": {...} }
        elseif ($request->has('device')) {
            $device = $request->input('device', []);
            $position = $request->input('position', []);
            $deviceId = $device['uniqueId'] ?? $device['id'] ?? null;
            $latitude = (float) ($position['latitude'] ?? 0);
            $longitude = (float) ($position['longitude'] ?? 0);
            $speed = (float) ($position['speed'] ?? 0);
            $heading = (float) ($position['course'] ?? 0);
            $accuracy = (float) ($position['accuracy'] ?? 0);
            $address = $position['address'] ?? '';
            $fixTime = $position['fixTime'] ?? null;
        }

        // Format 3: OsmAnd/location format
        // { "location": { "coords": {...}, "device_id": "KBL-912" } }
        elseif ($request->has('location')) {
            $location = $request->input('location', []);
            $coords = $location['coords'] ?? [];
            $deviceId = $location['device_id']
                ?? $request->input('device_id')
                ?? $request->query('id');
            $latitude = (float) ($coords['latitude'] ?? 0);
            $longitude = (float) ($coords['longitude'] ?? 0);
            $speed = (float) ($coords['speed'] ?? 0);
            $heading = (float) ($coords['heading'] ?? 0);
            $accuracy = (float) ($coords['accuracy'] ?? 0);
            $fixTime = $location['timestamp'] ?? null;
        }

        // Format 4: Query string format (OsmAnd protocol)
        // ?id=KBL-912&lat=34.5&lon=69.1&speed=0
        elseif ($request->query('id')) {
            $deviceId = $request->query('id');
            $latitude = (float) $request->query('lat', 0);
            $longitude = (float) $request->query('lon', 0);
            $speed = (float) $request->query('speed', 0);
            $heading = (float) $request->query('bearing', 0);
            $accuracy = (float) $request->query('accuracy', 0);
            $fixTime = $request->query('timestamp');
        }

        // ─── Validate ─────────────────────────────────────────────────────────

        if (!$deviceId || !$latitude || !$longitude) {
            \Log::warning('Traccar webhook missing fields', [
                'deviceId' => $deviceId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'all' => $request->all(),
            ]);
            return response()->json([
                'error' => 'Missing required fields',
                'received' => $request->all(),
            ], 422);
        }

        // ─── Find Vehicle ─────────────────────────────────────────────────────

        $vehicle = Vehicle::where('traccar_device_id', $deviceId)->first();

        if (!$vehicle) {
            \Log::warning('Traccar webhook vehicle not found', [
                'deviceId' => $deviceId,
            ]);
            return response()->json([
                'error' => 'Vehicle not found for device: ' . $deviceId,
            ], 404);
        }

        // ─── Find Active Booking ──────────────────────────────────────────────

        $booking = Booking::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['active', 'confirmed'])
            ->latest()
            ->first();

        // ─── Save Location History ────────────────────────────────────────────

        VehicleLocation::create([
            'vehicle_id' => $vehicle->id,
            'booking_id' => $booking?->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'heading' => $heading,
            'recorded_at' => $fixTime ? Carbon::parse($fixTime) : now(),
        ]);

        // ─── Update Vehicle Cache ─────────────────────────────────────────────

        $vehicle->update([
            'last_seen_at' => now(),
            'last_latitude' => $latitude,
            'last_longitude' => $longitude,
            'last_speed' => $speed,
            'last_address' => $address ?: null,
        ]);

        // ─── Broadcast To Admin Map ───────────────────────────────────────────

        broadcast(new VehicleLocationUpdated(
            vehicleId: $vehicle->id,
            vehicleName: $vehicle->brand . ' ' . $vehicle->model,
            latitude: $latitude,
            longitude: $longitude,
            speed: $speed,
            heading: $heading,
        ));

        \Log::info('Traccar webhook processed successfully', [
            'vehicle_id' => $vehicle->id,
            'lat' => $latitude,
            'lng' => $longitude,
        ]);

        return response()->json([
            'success' => true,
            'vehicle_id' => $vehicle->id,
            'lat' => $latitude,
            'lng' => $longitude,
        ]);
    }
}