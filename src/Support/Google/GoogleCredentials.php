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
        $base64 = config('mobile-pass.google.service_account_key_base64');
        if (! empty($base64)) {
            return (string) base64_decode((string) $base64);
        }

        $contents = config('mobile-pass.google.service_account_key_contents');
        if (! empty($contents)) {
            return (string) $contents;
        }

        $path = config('mobile-pass.google.service_account_key_path');
        if (! empty($path) && is_file((string) $path)) {
            return (string) file_get_contents((string) $path);
        }

        throw InvalidConfig::missingGoogleCredentials();
    }
}
