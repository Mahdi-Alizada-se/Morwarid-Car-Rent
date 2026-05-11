<?php

namespace App\Events;

use App\Models\VehicleLocation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly VehicleLocation $location,
        public readonly int $vehicleId,
        public readonly string $vehicleName,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('gps.' . $this->vehicleId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'vehicle_name' => $this->vehicleName,
            'latitude' => (float) $this->location->latitude,
            'longitude' => (float) $this->location->longitude,
            'speed' => $this->location->speed
                ? (float) $this->location->speed
                : null,
            'heading' => $this->location->heading
                ? (float) $this->location->heading
                : null,
            'recorded_at' => $this->location->recorded_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'location-updated';
    }
}