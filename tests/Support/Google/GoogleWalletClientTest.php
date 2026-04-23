<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletRequestFailed;
use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('inserts a class with the bearer token', function () {
    Http::fake([
        'example.com/walletobjects/v1/eventTicketClass' => Http::response(['id' => '3388.abc'], 200),
    ]);

    app(GoogleWalletClient::class)->insertClass('eventTicketClass', '3388.abc', ['foo' => 'bar']);

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->method() === 'POST'
        && $request->url() === 'https://example.com/walletobjects/v1/eventTicketClass'
        && $request['foo'] === 'bar'
    );
});

it('upgrades a 409 on insert to a patch', function () {
    Http::fakeSequence()
        ->push(['error' => ['code' => 409]], 409)
        ->push(['id' => '3388.abc'], 200);

    app(GoogleWalletClient::class)->insertClass('eventTicketClass', '3388.abc', ['foo' => 'baz']);

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/eventTicketClass/3388.abc')
        && $request->method() === 'PATCH'
    );
});

it('throws GoogleWalletRequestFailed on unexpected 4xx', function () {
    Http::fake([
        '*/eventTicketClass' => Http::response(['error' => 'nope'], 403),
    ]);

    app(GoogleWalletClient::class)->insertClass('eventTicketClass', '3388.abc', []);
})->throws(GoogleWalletRequestFailed::class);

it('patches an object', function () {
    Http::fake([
        '*/eventTicketObject/3388.abc' => Http::response(['id' => '3388.abc'], 200),
    ]);

    app(GoogleWalletClient::class)->patchObject('eventTicketObject', '3388.abc', ['state' => 'EXPIRED']);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH' && $request['state'] === 'EXPIRED');
});

it('lists classes and returns the resources array', function () {
    config()->set('mobile-pass.google.issuer_id', '3388');

    Http::fake([
        '*/eventTicketClass?*' => Http::response([
            'resources' => [['id' => '3388.a'], ['id' => '3388.b']],
        ], 200),
    ]);

    $classes = app(GoogleWalletClient::class)->listClasses('eventTicketClass');

    expect($classes)->toHaveCount(2);
    expect($classes[0]['id'])->toBe('3388.a');
});

it('retries on 5xx with backoff', function () {
    Http::fakeSequence()
        ->push('oops', 503)
        ->push('oops', 503)
        ->push(['id' => 'ok'], 200);

    app(GoogleWalletClient::class)->getClass('eventTicketClass', '3388.abc');

    Http::assertSentCount(3);
});
