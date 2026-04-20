<?php

use Firebase\JWT\JWT;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyGoogleCallbackRequest;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.callback_signing_key', GoogleFixtures::publicKey());
});

it('rejects a request with no Authorization header', function () {
    $middleware = new VerifyGoogleCallbackRequest;
    $request = request();

    $middleware->handle($request, fn ($request) => response('ok'));
})->throws(Illuminate\Auth\AuthenticationException::class);

it('accepts a request with a valid signed JWT', function () {
    $jwt = JWT::encode(
        ['iss' => 'google', 'iat' => time(), 'eventType' => 'save'],
        GoogleFixtures::privateKey(),
        'RS256'
    );

    $middleware = new VerifyGoogleCallbackRequest;
    $request = request();
    $request->setMethod('POST');
    $request->headers->set('Authorization', 'Bearer '.$jwt);

    $response = $middleware->handle($request, fn ($request) => response('ok'));

    expect($response->getContent())->toBe('ok');
});
