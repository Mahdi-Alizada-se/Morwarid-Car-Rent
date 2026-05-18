<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vehicle> $vehicles
 * @property-read int|null $vehicles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehicleCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VehicleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ─── Auto Slug ───────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (VehicleCategory $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (VehicleCategory $category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (
            static::where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'category_id');
    }
}