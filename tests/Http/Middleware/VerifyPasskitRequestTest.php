<?php

use Illuminate\Http\Request;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyApplePasskitRequest;
use Spatie\LaravelMobilePass\Models\MobilePass;

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

it('aborts with a 401 when an invalid auth token is provided', function () {
    config(['mobile-pass.apple.webservice.secret' => 'pass12345']);

    $request = Request::create(uri: '/test');
    $request->headers->set('Authorization', 'ApplePass incorrect');

    expectUnauthorized(fn () => (new VerifyApplePasskitRequest)->handle($request, fn () => response('Done!')));
});

it('responds with a plain 401 when the Apple auth header is wrong or missing', function (array $headers) {
    config(['mobile-pass.apple.webservice.secret' => 'pass12345']);

    $pass = MobilePass::factory()->create();

    $this
        ->get(route('mobile-pass.check-for-updates', [
            'passTypeId' => 'pass.com.example',
            'passSerial' => $pass->pass_serial,
        ]), $headers)
        ->assertUnauthorized();

    $this
        ->post(route('mobile-pass.register-device', [
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
            'passSerial' => $pass->pass_serial,
        ]), ['pushToken' => '12345'], $headers)
        ->assertUnauthorized();
})->with([
    'wrong header' => [['Authorization' => 'ApplePass incorrect']],
    'missing header' => [[]],
]);
