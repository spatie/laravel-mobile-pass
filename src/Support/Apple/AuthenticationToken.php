<?php

namespace Spatie\LaravelMobilePass\Support\Apple;

/**
 * Every pass carries its own `authenticationToken`, derived from the configured
 * secret and the pass serial. A token read from one downloaded pass therefore
 * can't be used to call the web service on behalf of any other pass.
 */
class AuthenticationToken
{
    public static function for(string $passSerial): ?string
    {
        $secret = self::secret();

        if ($secret === null) {
            return null;
        }

        return hash_hmac('sha256', $passSerial, $secret);
    }

    public static function isValid(string $passSerial, string $providedToken): bool
    {
        $expectedToken = self::for($passSerial);

        if ($expectedToken === null || $providedToken === '') {
            return false;
        }

        if (hash_equals($expectedToken, $providedToken)) {
            return true;
        }

        // Passes issued before per-pass tokens carry the raw shared secret. Accepting it
        // lets those devices fetch an updated pass, which then carries its own token.
        if (config('mobile-pass.apple.webservice.accept_shared_secret')) {
            return hash_equals((string) self::secret(), $providedToken);
        }

        return false;
    }

    protected static function secret(): ?string
    {
        $secret = config('mobile-pass.apple.webservice.secret');

        return is_string($secret) && $secret !== '' ? $secret : null;
    }
}
