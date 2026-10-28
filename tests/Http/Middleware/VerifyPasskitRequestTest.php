<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyApplePasskitRequest;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Apple\AuthenticationToken;

function passkitRequest(string $passSerial, ?string $authorization): Request
{
    $request = Request::create(uri: "/passes/pass.com.example/{$passSerial}");

    if ($authorization !== null) {
        $request->headers->set('Authorization', $authorization);
    }

    $request->setRouteResolver(function () use ($request) {
        return (new Illuminate\Routing\Route('GET', 'passes/{passTypeId}/{passSerial}', []))->bind($request);
    });

    return $request;
}

beforeEach(function () {
    config(['mobile-pass.apple.webservice.secret' => 'pass12345']);
});

it('handles the request when the pass token is provided', function () {
    $token = AuthenticationToken::for('serial-1');

    $response = (new VerifyApplePasskitRequest)->handle(
        passkitRequest('serial-1', "ApplePass {$token}"),
        fn () => response('Done!'),
    );

    expect($response->status())->toBe(200);
});

it('aborts with a 401 when an invalid auth token is provided', function () {
    expectUnauthorized(fn () => (new VerifyApplePasskitRequest)->handle(
        passkitRequest('serial-1', 'ApplePass incorrect'),
        fn () => response('Done!'),
    ));
});

it('rejects the token of another pass', function () {
    $tokenOfOtherPass = AuthenticationToken::for('serial-2');

    expectUnauthorized(fn () => (new VerifyApplePasskitRequest)->handle(
        passkitRequest('serial-1', "ApplePass {$tokenOfOtherPass}"),
        fn () => response('Done!'),
    ));
});

it('rejects the raw shared secret by default', function () {
    expectUnauthorized(fn () => (new VerifyApplePasskitRequest)->handle(
        passkitRequest('serial-1', 'ApplePass pass12345'),
        fn () => response('Done!'),
    ));
});

it('accepts the raw shared secret when legacy passes are allowed', function () {
    config(['mobile-pass.apple.webservice.accept_shared_secret' => true]);

    $response = (new VerifyApplePasskitRequest)->handle(
        passkitRequest('serial-1', 'ApplePass pass12345'),
        fn () => response('Done!'),
    );

    expect($response->status())->toBe(200);
});

it('rejects every request when no secret is configured', function () {
    config(['mobile-pass.apple.webservice.secret' => null]);

    expectUnauthorized(fn () => (new VerifyApplePasskitRequest)->handle(
        passkitRequest('serial-1', 'ApplePass '),
        fn () => response('Done!'),
    ));
});

it('responds with a plain 401 when the Apple auth header is wrong or missing', function (array $headers, bool $hasLoginRoute) {
    if ($hasLoginRoute) {
        Route::get('login', fn () => 'Login')->name('login');
    }

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
])->with([
    'without a login route' => false,
    'with a login route' => true,
]);

it('lets the pass token through on the real routes', function () {
    $pass = MobilePass::factory()->create();

    $this
        ->get(route('mobile-pass.check-for-updates', [
            'passTypeId' => 'pass.com.example',
            'passSerial' => $pass->pass_serial,
        ]), ['Authorization' => 'ApplePass '.AuthenticationToken::for($pass->pass_serial)])
        ->assertSuccessful();
});
