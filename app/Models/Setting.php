<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Get a setting value by key with an optional default.
     * Includes simple cache for performance.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Clear cache when setting is updated.
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            Cache::forget("setting_{$setting->key}");
        });
    }
}
