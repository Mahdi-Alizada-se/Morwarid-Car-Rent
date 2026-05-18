<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $brand
 * @property string $model
 * @property int $year
 * @property int $category_id
 * @property string $license_plate
 * @property string $color
 * @property int $seats
 * @property string $fuel_type
 * @property string $transmission
 * @property string $status
 * @property int $odometer
 * @property string|null $description
 * @property string|null $thumbnail
 * @property array<array-key, mixed>|null $features
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read \App\Models\VehicleCategory $category
 * @property-read string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleLocation> $latestLocation
 * @property-read int|null $latest_location_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehicleLocation> $locations
 * @property-read int|null $locations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PricingRule> $pricingRules
 * @property-read int|null $pricing_rules_count
 * @method static Builder<static>|Vehicle available()
 * @method static Builder<static>|Vehicle booked()
 * @method static Builder<static>|Vehicle byCategory(int $categoryId)
 * @method static Builder<static>|Vehicle byFuelType(string $fuelType)
 * @method static Builder<static>|Vehicle byTransmission(string $transmission)
 * @method static Builder<static>|Vehicle byYear(int $year)
 * @method static Builder<static>|Vehicle maintenance()
 * @method static Builder<static>|Vehicle newModelQuery()
 * @method static Builder<static>|Vehicle newQuery()
 * @method static Builder<static>|Vehicle onlyTrashed()
 * @method static Builder<static>|Vehicle query()
 * @method static Builder<static>|Vehicle search(string $term)
 * @method static Builder<static>|Vehicle whereBrand($value)
 * @method static Builder<static>|Vehicle whereCategoryId($value)
 * @method static Builder<static>|Vehicle whereColor($value)
 * @method static Builder<static>|Vehicle whereCreatedAt($value)
 * @method static Builder<static>|Vehicle whereDeletedAt($value)
 * @method static Builder<static>|Vehicle whereDescription($value)
 * @method static Builder<static>|Vehicle whereFeatures($value)
 * @method static Builder<static>|Vehicle whereFuelType($value)
 * @method static Builder<static>|Vehicle whereId($value)
 * @method static Builder<static>|Vehicle whereLicensePlate($value)
 * @method static Builder<static>|Vehicle whereModel($value)
 * @method static Builder<static>|Vehicle whereOdometer($value)
 * @method static Builder<static>|Vehicle whereSeats($value)
 * @method static Builder<static>|Vehicle whereStatus($value)
 * @method static Builder<static>|Vehicle whereThumbnail($value)
 * @method static Builder<static>|Vehicle whereTransmission($value)
 * @method static Builder<static>|Vehicle whereUpdatedAt($value)
 * @method static Builder<static>|Vehicle whereYear($value)
 * @method static Builder<static>|Vehicle withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Vehicle withoutTrashed()
 * @mixin \Eloquent
 */
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