<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    /**
     * Retrieve a translation value by locale, group and key.
     */
    public static function translate(string $locale, string $group, string $key, string $fallback = ''): string
    {
        $translation = static::where('locale', $locale)
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        return $translation?->value ?? $fallback;
    }
}