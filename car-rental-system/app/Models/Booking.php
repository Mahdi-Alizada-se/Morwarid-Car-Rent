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

    // ─── Status Constants ─────────────────────────────────────────────────────

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
        'cancellation_fee',
        'cancellation_fee_paid',
        'booked_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'datetime',
            'return_date' => 'datetime',
            'actual_return_date' => 'datetime',
            'cancelled_at' => 'datetime',
            'booked_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'cancellation_fee' => 'decimal:2',
            'cancellation_fee_paid' => 'boolean',
        ];
    }

    // ─── Auto Reference Code ──────────────────────────────────────────────────

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
            $code = 'CR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (static::where('reference_code', $code)->exists());

        return $code;
    }

    // ─── Cancellation Logic ───────────────────────────────────────────────────

    /**
     * Can this booking be cancelled?
     * Rules:
     * - Cannot cancel if already cancelled
     * - Cannot cancel if completed
     * - Cannot cancel if return date has passed (expired)
     */
    public function canBeCancelled(): bool
    {
        if (in_array($this->status, ['cancelled', 'completed'])) {
            return false;
        }

        // Cannot cancel expired bookings
        if ($this->return_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Is there a cancellation fee?
     * Rules:
     * - Pending bookings: FREE if within 2 hours of booking
     * - Confirmed bookings: 1 day rate fee if after 2 hours
     * - Active bookings: charge for days already used
     */
    public function hasCancellationFee(): bool
    {
        if (!$this->canBeCancelled())
            return false;

        // Pending within 2 hours = free
        if ($this->status === 'pending') {
            $reference = $this->booked_at ?? $this->created_at;
            return now()->diffInHours($reference) > 2;
        }

        // Confirmed or active = always has fee
        return in_array($this->status, ['confirmed', 'active']);
    }

    /**
     * Calculate cancellation fee amount:
     * - Pending (after 2h): 1 day rate
     * - Confirmed (after 2h): 1 day rate
     * - Active (rental started): days used × daily rate
     */
    public function getCancellationFeeAmount(): float
    {
        if (!$this->hasCancellationFee())
            return 0.0;

        $dailyRate = $this->vehicle
                ?->pricingRules()
            ->where('type', 'daily')
            ->where('is_active', true)
            ->first()
                ?->base_rate ?? 0;

        if ($this->status === 'active') {
            // Charge for days already used
            $daysUsed = max(1, (int) $this->pickup_date->diffInDays(now()));
            return (float) ($daysUsed * $dailyRate);
        }

        // Confirmed or pending after 2h = 1 day fee
        return (float) $dailyRate;
    }

    /**
     * Get human readable cancellation fee description
     */
    public function getCancellationFeeDescription(): string
    {
        if ($this->status === 'active') {
            $daysUsed = max(1, $this->pickup_date->diffInDays(now()));
            return __('bookings.cancellation_fee') . ': AFN '
                . number_format($this->getCancellationFeeAmount())
                . ' (' . $daysUsed . ' ' . __('bookings.day_rate') . ')';
        }

        return __('bookings.cancellation_fee') . ': AFN '
            . number_format($this->getCancellationFeeAmount())
            . ' (' . __('bookings.one_day_rate') . ')';
    }

    public function cancel(string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $fee = $this->getCancellationFeeAmount();

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancellation_fee' => $fee,
            'cancellation_fee_paid' => false,
        ]);

        // Free the vehicle
        $this->vehicle?->update(['status' => 'available']);

        return true;
    }

    public function getDurationInDaysAttribute(): int
    {
        return (int) $this->pickup_date->diffInDays($this->return_date) ?: 1;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

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