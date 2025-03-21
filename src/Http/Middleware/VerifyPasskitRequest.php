<?php

namespace Spatie\LaravelMobilePass\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class VerifyPasskitRequest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Authorization') !== $this->expectedAuthorizationValue()) {
            throw new AuthenticationException('Invalid Passkit authorization header.');
        }

        return $next($request);
    }

    protected function expectedAuthorizationValue(): string
    {
        return 'ApplePass ' . config('mobile-pass.apple.webservice.secret');
    }
}
