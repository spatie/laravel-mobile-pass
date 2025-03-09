<?php

namespace Spatie\LaravelMobileWallet\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class PasskitServerVerify
{
    protected const AUTH_PREFIX = "ApplePass";

    /**
     * Gets what we expect the authorization header value to be.
     *
     * @return string
     */
    protected function getExpectedAuthorizationHeader(): string
    {
        return self::AUTH_PREFIX . " " . config('mobile-pass.apple.webservice.secret');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Authorization') === $this->getExpectedAuthorizationHeader()) {
            return $next($request);
        }

        throw new AuthenticationException('Invalid authorization header.');
    }
}
