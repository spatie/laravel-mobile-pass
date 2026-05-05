<?php

namespace Spatie\LaravelMobilePass\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class VerifyApplePasskitRequest
{
    public function handle(Request $request, Closure $next): mixed
    {
        $providedAuthorization = (string) $request->header('Authorization');

        if (! hash_equals($this->expectedAuthorizationValue(), $providedAuthorization)) {
            throw new AuthenticationException('Invalid Passkit authorization header.');
        }

        return $next($request);
    }

    protected function expectedAuthorizationValue(): string
    {
        return 'ApplePass '.config('mobile-pass.apple.webservice.secret');
    }
}
