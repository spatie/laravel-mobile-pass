<?php

namespace Spatie\LaravelMobilePass\Support\Google;

use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;

class GoogleCredentials
{
    /** @return array<string, mixed> */
    public static function key(): array
    {
        return json_decode(static::rawKeyContents(), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function privateKey(): string
    {
        return static::key()['private_key'];
    }

    public static function clientEmail(): string
    {
        return static::key()['client_email'];
    }

    public static function issuerId(): string
    {
        return (string) config('mobile-pass.google.issuer_id');
    }

    protected static function rawKeyContents(): string
    {
        $contents = (string) config('mobile-pass.google.service_account_key');

        if ($contents !== '') {
            return str_starts_with(ltrim($contents), '{')
                ? $contents
                : (string) base64_decode($contents);
        }

        $path = (string) config('mobile-pass.google.service_account_key_path');

        if ($path !== '' && is_file($path)) {
            return (string) file_get_contents($path);
        }

        throw InvalidConfig::missingGoogleCredentials();
    }
}
