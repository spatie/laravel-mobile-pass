<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Concerns;

use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Exceptions\InvalidPass;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;

trait BuildsGoogleIds
{
    /**
     * Google only allows alphanumerics, `.`, `_` and `-` in the part of a
     * class or object id that follows the issuer id.
     */
    protected static function ensureValidSuffix(string $suffix, string $key = 'suffix'): string
    {
        if (preg_match('/^[A-Za-z0-9._-]+$/', $suffix) !== 1) {
            throw InvalidPass::withMessages([
                $key => "The Google Wallet id suffix `{$suffix}` is invalid. Use only letters, digits, `.`, `_` and `-`.",
            ]);
        }

        return $suffix;
    }

    protected static function qualifiedGoogleId(string $suffix): string
    {
        $issuerId = GoogleCredentials::issuerId();

        if ($issuerId === '') {
            throw new InvalidConfig(
                'No Google Wallet issuer id is configured. Set MOBILE_PASS_GOOGLE_ISSUER_ID '
                .'(the `mobile-pass.google.issuer_id` config value) before building Google classes or objects.'
            );
        }

        return $issuerId.'.'.$suffix;
    }
}
