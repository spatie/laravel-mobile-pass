<?php

namespace Spatie\LaravelMobilePass\Support;

class WifiUri
{
    public static function build(
        string $ssid,
        ?string $password = null,
        bool $hidden = false,
    ): string {
        $hasPassword = $password !== null && $password !== '';

        $parts = [
            'S:'.self::escape($ssid),
            'T:'.($hasPassword ? 'WPA' : 'nopass'),
        ];

        if ($hasPassword) {
            $parts[] = 'P:'.self::escape($password);
        }

        if ($hidden) {
            $parts[] = 'H:true';
        }

        return 'WIFI:'.implode(';', $parts).';;';
    }

    protected static function escape(string $value): string
    {
        return addcslashes($value, '\\;,:"');
    }
}
