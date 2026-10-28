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

it('upgrades a 409 on insert to a full PUT update', function () {
    Http::fakeSequence()
        ->push(['error' => ['code' => 409]], 409)
        ->push(['id' => '3388.abc'], 200);

    app(GoogleWalletClient::class)->insertClass('eventTicketClass', '3388.abc', ['foo' => 'baz']);

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => $request->url() === 'https://example.com/walletobjects/v1/eventTicketClass/3388.abc'
        && $request->method() === 'PUT'
        && $request['foo'] === 'baz'
        && $request['id'] === '3388.abc'
    );
    Http::assertNotSent(fn ($request) => $request->method() === 'PATCH');
});

it('upgrades a 409 on object insert to a full PUT update', function () {
    Http::fakeSequence()
        ->push(['error' => ['code' => 409]], 409)
        ->push(['id' => '3388.obj'], 200);

    app(GoogleWalletClient::class)->insertObject('eventTicketObject', '3388.obj', ['state' => 'ACTIVE']);

    Http::assertSent(fn ($request) => $request->url() === 'https://example.com/walletobjects/v1/eventTicketObject/3388.obj'
        && $request->method() === 'PUT'
    );
});

it('updates classes and objects with PUT', function () {
    Http::fake(['*' => Http::response([], 200)]);

    $client = app(GoogleWalletClient::class);
    $client->updateClass('eventTicketClass', '3388.abc', ['foo' => 'bar']);
    $client->updateObject('eventTicketObject', '3388.obj', ['state' => 'ACTIVE']);

    Http::assertSent(fn ($request) => $request->method() === 'PUT'
        && str_ends_with($request->url(), '/eventTicketClass/3388.abc'));
    Http::assertSent(fn ($request) => $request->method() === 'PUT'
        && str_ends_with($request->url(), '/eventTicketObject/3388.obj'));
});

it('url-encodes ids placed in the request path', function () {
    Http::fake(['*' => Http::response([], 200)]);

    $client = app(GoogleWalletClient::class);
    $client->getClass('eventTicketClass', '3388.a/../b?x=1');
    $client->patchObject('eventTicketObject', '3388.a/b', ['state' => 'EXPIRED']);
    $client->updateClass('eventTicketClass', '3388.a#b', []);

    Http::assertSent(fn ($request) => $request->method() === 'GET'
        && $request->url() === 'https://example.com/walletobjects/v1/eventTicketClass/3388.a%2F..%2Fb%3Fx%3D1');
    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request->url() === 'https://example.com/walletobjects/v1/eventTicketObject/3388.a%2Fb');
    Http::assertSent(fn ($request) => $request->method() === 'PUT'
        && $request->url() === 'https://example.com/walletobjects/v1/eventTicketClass/3388.a%23b');
});

it('url-encodes the issuer id when listing classes', function () {
    config()->set('mobile-pass.google.issuer_id', '33&88');

    Http::fake(['*' => Http::response(['resources' => []], 200)]);

    app(GoogleWalletClient::class)->listClasses('eventTicketClass');

    Http::assertSent(fn ($request) => $request->url() === 'https://example.com/walletobjects/v1/eventTicketClass?issuerId=33%2688');
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
