<?php

namespace Spatie\LaravelMobilePass\Tests\TestSupport\Google;

class GoogleFixtures
{
    public static function serviceAccountPath(): string
    {
        return __DIR__.'/../google-service-account.json';
    }

    public static function serviceAccountContents(): string
    {
        return (string) file_get_contents(self::serviceAccountPath());
    }

    public static function privateKey(): string
    {
        $decoded = json_decode(self::serviceAccountContents(), true, flags: JSON_THROW_ON_ERROR);

        return $decoded['private_key'];
    }

    public static function publicKey(): string
    {
        return (string) file_get_contents(__DIR__.'/../google-public-key.pem');
    }
}
