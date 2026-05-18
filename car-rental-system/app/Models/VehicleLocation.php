<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property int|null $booking_id
 * @property numeric $latitude
 * @property numeric $longitude
 * @property numeric|null $speed
 * @property numeric|null $heading
 * @property \Illuminate\Support\Carbon $recorded_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \App\Models\Vehicle|null $vehicle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation whereHeading($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation whereRecordedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation whereSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleLocation whereVehicleId($value)
 * @mixin \Eloquent
 */
class VehicleLocation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'booking_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'speed' => 'decimal:2',
            'heading' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}