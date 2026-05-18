<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $reference_code
 * @property int $customer_id
 * @property int $vehicle_id
 * @property \Illuminate\Support\Carbon $pickup_date
 * @property \Illuminate\Support\Carbon $return_date
 * @property \Illuminate\Support\Carbon|null $actual_return_date
 * @property string|null $pickup_location
 * @property string|null $return_location
 * @property string $status
 * @property numeric $total_amount
 * @property string $currency
 * @property string|null $payment_method
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property numeric|null $cancellation_fee
 * @property bool $cancellation_fee_paid
 * @property \Illuminate\Support\Carbon|null $booked_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $customer
 * @property-read int $duration_in_days
 * @property-read \App\Models\Payment|null $latestPayment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleLocation> $locations
 * @property-read int|null $locations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Vehicle|null $vehicle
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereActualReturnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereBookedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCancellationFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCancellationFeePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking wherePickupDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking wherePickupLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereReferenceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereReturnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereReturnLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereVehicleId($value)
 * @mixin \Eloquent
 */
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
        'cancellation_fee',        // ← ADD
        'cancellation_fee_paid',   // ← ADD
        'booked_at',               // ← ADD
    ];
    protected function casts(): array
    {
        return [
            'pickup_date' => 'datetime',
            'return_date' => 'datetime',
            'actual_return_date' => 'datetime',
            'cancelled_at' => 'datetime',
            'booked_at' => 'datetime',       // ← ADD
            'total_amount' => 'decimal:2',
            'cancellation_fee' => 'decimal:2',      // ← ADD
            'cancellation_fee_paid' => 'boolean',        // ← ADD
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
        // Always cancellable — fee may apply after 5 hours
        return true;
    }

    public function hasCancellationFee(): bool
    {
        $reference = $this->booked_at ?? $this->created_at;

        return $reference
            && now()->diffInHours($reference) > 5
            && $this->status !== 'cancelled';
    }

    public function getCancellationFeeAmount(): float
    {
        $rule = $this->vehicle
                ?->pricingRules()
            ->where('type', 'daily')
            ->where('is_active', true)
            ->first();

        return $rule ? (float) $rule->base_rate : 0.0;
    }

    // public function hasCancellationFee(): bool
    // {
    //     if (!$this->booked_at) {
    //         return false;
    //     }

    //     return (now()->diffInHours($this->booked_at) > 5)
    //         && $this->status !== 'cancelled';
    // }

    // public function getCancellationFeeAmount(): float
    // {
    //     $rule = $this->vehicle
    //             ?->pricingRules()
    //         ->where('type', 'daily')
    //         ->where('is_active', true)
    //         ->first();

    //     return $rule ? (float) $rule->base_rate : 0.0;
    // }

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