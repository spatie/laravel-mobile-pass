<?php

namespace Spatie\LaravelMobilePass\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Support\Apple\AuthenticationToken;

class VerifyApplePasskitRequest
{
    public function handle(Request $request, Closure $next): mixed
    {
        $providedAuthorization = (string) $request->header('Authorization');

        if (! Str::startsWith($providedAuthorization, 'ApplePass ')) {
            abort(401);
        }

        $passSerial = (string) $request->route('passSerial');

        $providedToken = Str::after($providedAuthorization, 'ApplePass ');

        if ($passSerial === '' || ! AuthenticationToken::isValid($passSerial, $providedToken)) {
            abort(401);
        }

        return $next($request);
    }
}
