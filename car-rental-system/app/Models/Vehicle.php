<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand',
        'model',
        'year',
        'category_id',
        'license_plate',
        'color',
        'seats',
        'fuel_type',
        'transmission',
        'status',
        'odometer',
        'description',
        'thumbnail',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'year' => 'integer',
            'seats' => 'integer',
            'odometer' => 'integer',
        ];
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeBooked(Builder $query): Builder
    {
        return $query->where('status', 'booked');
    }

    public function scopeMaintenance(Builder $query): Builder
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByFuelType(Builder $query, string $fuelType): Builder
    {
        return $query->where('fuel_type', $fuelType);
    }

    public function scopeByTransmission(Builder $query, string $transmission): Builder
    {
        return $query->where('transmission', $transmission);
    }

    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('brand', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhere('license_plate', 'like', "%{$term}%")
                ->orWhere('color', 'like', "%{$term}%");
        });
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderBy('order');
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(VehicleLocation::class);
    }

    public function latestLocation(): HasMany
    {
        return $this->hasMany(VehicleLocation::class)->latest('recorded_at')->limit(1);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->year} {$this->brand} {$this->model}";
    }
}