<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $vehicleId,
        public readonly string $vehicleName,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly float $speed,
        public readonly float $heading,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('gps.' . $this->vehicleId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'VehicleLocationUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'vehicle_id' => $this->vehicleId,
            'vehicle_name' => $this->vehicleName,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'speed' => $this->speed,
            'heading' => $this->heading,
            'recorded_at' => now()->toISOString(),
        ];
    }
}