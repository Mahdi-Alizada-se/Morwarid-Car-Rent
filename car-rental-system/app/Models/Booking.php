<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    // ─── Status Constants ─────────────────────────────────────────────────────────

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'reference_code',
        'customer_id',
        'vehicle_id',
        'pickup_date',
        'return_date',
        'actual_return_date',
        'pickup_location',
        'return_location',
        'status',
        'total_amount',
        'currency',
        'notes',
        'cancelled_at',
        'cancellation_reason',
        'payment_method',
    ];
    protected function casts(): array
    {
        return [
            'pickup_date' => 'datetime',
            'return_date' => 'datetime',
            'actual_return_date' => 'datetime',
            'cancelled_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    // ─── Auto Reference Code ──────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->reference_code)) {
                $booking->reference_code = static::generateReferenceCode();
            }
        });
    }

    public static function generateReferenceCode(): string
    {
        do {
            $code = 'BK-' . strtoupper(Str::random(8));
        } while (static::where('reference_code', $code)->exists());

        return $code;
    }

    // ─── Business Logic ───────────────────────────────────────────────────────────

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function cancel(string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        // Free the vehicle
        $this->vehicle?->update(['status' => 'available']);

        return true;
    }

    public function getDurationInDaysAttribute(): int
    {
        return (int) $this->pickup_date->diffInDays($this->return_date) ?: 1;
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function locations(): HasMany
    {
        return $this->hasMany(VehicleLocation::class);
    }
}