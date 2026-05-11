<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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