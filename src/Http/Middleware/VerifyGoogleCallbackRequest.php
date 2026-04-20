<?php

namespace Spatie\LaravelMobilePass\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Throwable;

class VerifyGoogleCallbackRequest
{
    public function handle(Request $request, Closure $next): mixed
    {
        $authorization = (string) $request->header('Authorization');

        if (! str_starts_with($authorization, 'Bearer ')) {
            throw new AuthenticationException('Missing bearer token on Google callback.');
        }

        $jwt = substr($authorization, 7);
        $signingKey = (string) config('mobile-pass.google.callback_signing_key');

        if ($signingKey === '') {
            throw new AuthenticationException('No callback signing key configured.');
        }

        try {
            $decoded = JWT::decode($jwt, new Key($signingKey, 'RS256'));
        } catch (Throwable $exception) {
            throw new AuthenticationException('Invalid Google callback JWT: '.$exception->getMessage());
        }

        $request->attributes->set('google_callback_claims', (array) $decoded);

        return $next($request);
    }
}
