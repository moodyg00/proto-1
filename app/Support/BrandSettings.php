<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BrandSettings
{
    protected const DEFAULT_NAME = 'Moody Home Services, LLC';

    protected static ?array $cache = null;

    public static function get(bool $refresh = false): array
    {
        if (! $refresh && static::$cache !== null) {
            return static::$cache;
        }

        $branding = [
            'brand_name' => static::DEFAULT_NAME,
            'logo_path' => null,
            'logo_url' => asset('images/moody-home-services-mark.svg'),
        ];

        try {
            if (! Schema::hasTable('settings')) {
                return static::$cache = $branding;
            }

            $setting = Setting::query()
                ->where('module', 'business')
                ->where('key', 'branding')
                ->first();

            if (! $setting || ! is_array($setting->value)) {
                return static::$cache = $branding;
            }

            $logoPath = $setting->value['logo_path'] ?? null;

            return static::$cache = [
                'brand_name' => $setting->value['brand_name'] ?? static::DEFAULT_NAME,
                'logo_path' => $logoPath,
                'logo_url' => filled($logoPath)
                    ? Storage::disk('public')->url($logoPath)
                    : $branding['logo_url'],
            ];
        } catch (Throwable) {
            return static::$cache = $branding;
        }
    }

    public static function name(): string
    {
        return static::get()['brand_name'];
    }

    public static function logoUrl(): string
    {
        return static::get()['logo_url'];
    }

    public static function clearCache(): void
    {
        static::$cache = null;
    }
}