<?php

namespace Spatie\LaravelMobilePass\Support\Apple;

use Illuminate\Encryption\MissingAppKeyException;

class AuthenticationToken
{
    /**
     * Older versions of this package put the web service secret itself in
     * every pass, so the secret alone must not be enough to derive a token.
     * That is why the app key, which never leaves the server, is part of the key.
     */
    public static function forSerial(string $passSerial): string
    {
        $appKey = config('app.key');

        if (! is_string($appKey) || $appKey === '') {
            throw new MissingAppKeyException;
        }

        return hash_hmac('sha256', $passSerial, config('mobile-pass.apple.webservice.secret').$appKey);
    }
}
