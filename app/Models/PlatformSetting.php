<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('platform_setting_' . $key, function () use ($key, $default) {
            try {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            } catch (\Throwable $e) {
                return $default;
            }
        });
    }

    /**
     * Set a setting by key.
     */
    public static function set(string $key, mixed $value): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget('platform_setting_' . $key);

        return $setting;
    }

    /**
     * Forget/delete a setting by key.
     */
    public static function forget(string $key): bool
    {
        Cache::forget('platform_setting_' . $key);
        return (bool) static::where('key', $key)->delete();
    }
}
