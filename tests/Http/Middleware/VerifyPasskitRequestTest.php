<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyApplePasskitRequest;

it('handles the request when a valid auth token is provided', function () {
    config(['mobile-pass.apple.webservice.secret' => 'pass12345']);

    $request = Request::create(uri: '/test');
    $request->headers->set('Authorization', 'ApplePass pass12345');

    $response = (new VerifyApplePasskitRequest)
        ->handle(
            $request,
            fn () => response('Done!')
        );

    $this->assertEquals(200, $response->status());
});

it('returns 403 when an invalid auth token is provided', function () {
    config(['mobile-pass.apple.webservice.secret' => 'pass12345']);

    $request = Request::create(uri: '/test');
    $request->headers->set('Authorization', 'ApplePass incorrect');

    $this->assertThrows(
        fn () => (new VerifyApplePasskitRequest)
            ->handle(
                $request,
                fn () => response('Done!')
            ),
        AuthenticationException::class,
    );
});
