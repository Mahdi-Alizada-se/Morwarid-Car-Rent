<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $vehicle_id
 * @property string $type
 * @property numeric $base_rate
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $date_from
 * @property \Illuminate\Support\Carbon|null $date_to
 * @property numeric $multiplier
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $effective_rate
 * @property-read \App\Models\Vehicle|null $vehicle
 * @method static Builder<static>|PricingRule active()
 * @method static Builder<static>|PricingRule applicableOn(string $date)
 * @method static Builder<static>|PricingRule newModelQuery()
 * @method static Builder<static>|PricingRule newQuery()
 * @method static Builder<static>|PricingRule ofType(string $type)
 * @method static Builder<static>|PricingRule query()
 * @method static Builder<static>|PricingRule whereBaseRate($value)
 * @method static Builder<static>|PricingRule whereCreatedAt($value)
 * @method static Builder<static>|PricingRule whereCurrency($value)
 * @method static Builder<static>|PricingRule whereDateFrom($value)
 * @method static Builder<static>|PricingRule whereDateTo($value)
 * @method static Builder<static>|PricingRule whereId($value)
 * @method static Builder<static>|PricingRule whereIsActive($value)
 * @method static Builder<static>|PricingRule whereMultiplier($value)
 * @method static Builder<static>|PricingRule whereType($value)
 * @method static Builder<static>|PricingRule whereUpdatedAt($value)
 * @method static Builder<static>|PricingRule whereVehicleId($value)
 * @mixin \Eloquent
 */
class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'type',
        'base_rate',
        'currency',
        'date_from',
        'date_to',
        'multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_rate' => 'decimal:2',
            'multiplier' => 'decimal:2',
            'date_from' => 'date',
            'date_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeApplicableOn(Builder $query, string $date): Builder
    {
        return $query->where(function (Builder $q) use ($date) {
            $q->whereNull('date_from')->orWhere('date_from', '<=', $date);
        })->where(function (Builder $q) use ($date) {
            $q->whereNull('date_to')->orWhere('date_to', '>=', $date);
        });
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    public function getEffectiveRateAttribute(): float
    {
        return round((float) $this->base_rate * (float) $this->multiplier, 2);
    }
}