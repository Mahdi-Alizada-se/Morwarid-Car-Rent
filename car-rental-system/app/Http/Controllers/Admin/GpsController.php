<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GpsController extends Controller
{
    // ─── Main GPS Map Page ────────────────────────────────────────────────────

    public function index(): View
    {
        $trackedVehicles = Vehicle::whereNotNull('traccar_device_id')
            ->select(
                'id',
                'brand',
                'model',
                'license_plate',
                'status',
                'color',
                'last_latitude',
                'last_longitude',
                'last_speed',
                'last_seen_at',
                'last_address',
                'traccar_device_id',
            )
            ->get()
            ->map(function ($v) {
                $v->last_seen_at_human = $v->last_seen_at?->diffForHumans();
                $v->is_online = $v->last_seen_at &&
                    $v->last_seen_at->gt(now()->subMinutes(10));
                $v->status_color = match (true) {
                    !$v->last_seen_at => 'gray',
                    $v->last_seen_at->lt(now()->subMinutes(10)) => 'gray',
                    $v->last_speed > 5 => 'green',
                    default => 'orange',
                };
                return $v;
            });

        $unTrackedVehicles = Vehicle::whereNull('traccar_device_id')
            ->select('id', 'brand', 'model', 'license_plate')
            ->get();

        return view('admin.gps.index', compact('trackedVehicles', 'unTrackedVehicles'));
    }

    // ─── Trip History ─────────────────────────────────────────────────────────

    public function history(Vehicle $vehicle): JsonResponse
    {
        $locations = VehicleLocation::where('vehicle_id', $vehicle->id)
            ->where('recorded_at', '>=', now()->subHours(24))
            ->orderBy('recorded_at', 'asc')
            ->select('latitude', 'longitude', 'speed', 'recorded_at')
            ->get()
            ->map(fn($l) => [
                'lat' => (float) $l->latitude,
                'lng' => (float) $l->longitude,
                'speed' => (float) $l->speed,
                'recorded_at' => $l->recorded_at->format('H:i:s'),
            ]);

        return response()->json(['success' => true, 'data' => $locations]);
    }

    // ─── Setup Page ───────────────────────────────────────────────────────────

    public function setup(Vehicle $vehicle): View
    {
        return view('admin.gps.setup', compact('vehicle'));
    }

    // ─── Save Device ID ───────────────────────────────────────────────────────

    public function saveDevice(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'traccar_device_id' => [
                'required',
                'string',
                'max:100',
                'unique:vehicles,traccar_device_id,' . $vehicle->id,
            ],
            'traccar_device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $vehicle->update([
            'traccar_device_id' => $request->traccar_device_id,
            'traccar_device_name' => $request->traccar_device_name
                ?? $vehicle->brand . ' ' . $vehicle->model,
        ]);

        return redirect()
            ->route('admin.gps.index')
            ->with('success', 'GPS tracker configured for ' . $vehicle->brand . ' ' . $vehicle->model);
    }

    // ─── Remove Device ────────────────────────────────────────────────────────

    public function removeDevice(Vehicle $vehicle)
    {
        $vehicle->update([
            'traccar_device_id' => null,
            'traccar_device_name' => null,
        ]);

        return redirect()->back()->with('success', 'GPS tracker removed.');
    }
}