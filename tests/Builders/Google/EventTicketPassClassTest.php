<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;
use Spatie\LaravelMobilePass\Exceptions\PlatformDoesntSupport;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('computes the full id from issuer + suffix', function () {
    expect(EventTicketPassClass::make('ts-2026')->id())->toBe('3388.ts-2026');
});

it('saves the expected payload to Google', function () {
    Http::fake(['*/eventTicketClass' => Http::response([], 200)]);

    EventTicketPassClass::make('ts-2026')
        ->setEventName('The Eras Tour')
        ->setVenueName('King Baudouin Stadium')
        ->setLogoUrl('https://cdn.example.com/logo.png')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['id'])->toBe('3388.ts-2026');
        expect($request['eventName']['defaultValue']['value'])->toBe('The Eras Tour');
        expect($request['eventName']['defaultValue']['language'])->toBe('en-US');
        expect($request['venue']['name']['defaultValue']['value'])->toBe('King Baudouin Stadium');
        expect($request['venue']['name']['defaultValue']['language'])->toBe('en-US');
        expect($request['logo']['sourceUri']['uri'])->toBe('https://cdn.example.com/logo.png');

        return true;
    });
});

it('retire() is unsupported and sends nothing to Google', function () {
    Http::fake();

    expect(fn () => EventTicketPassClass::make('ts-2026')->retire())
        ->toThrow(PlatformDoesntSupport::class);

    Http::assertNothingSent();
});

it('replaces an existing class with a full PUT and resets reviewStatus to UNDER_REVIEW', function () {
    Http::fakeSequence()
        ->push(['error' => ['code' => 409]], 409)
        ->push(['id' => '3388.ts-2026', 'reviewStatus' => 'UNDER_REVIEW'], 200);

    EventTicketPassClass::make('ts-2026')
        ->setEventName('The Eras Tour')
        ->save();

    Http::assertSentCount(2);
    Http::assertSent(function ($request) {
        return $request->method() === 'PUT'
            && str_ends_with($request->url(), '/eventTicketClass/3388.ts-2026')
            && $request['reviewStatus'] === 'UNDER_REVIEW'
            && $request['eventName']['defaultValue']['value'] === 'The Eras Tour';
    });
});

it('find() returns null on 404', function () {
    Http::fake(['*/eventTicketClass/3388.missing' => Http::response([], 404)]);

    expect(EventTicketPassClass::find('missing'))->toBeNull();
});

it('all() returns a collection hydrated from resources', function () {
    Http::fake(['*/eventTicketClass*' => Http::response([
        'resources' => [
            ['id' => '3388.a', 'eventName' => ['defaultValue' => ['language' => 'en-US', 'value' => 'A']]],
            ['id' => '3388.b', 'eventName' => ['defaultValue' => ['language' => 'en-US', 'value' => 'B']]],
        ],
    ], 200)]);

    $classes = EventTicketPassClass::all();

    expect($classes)->toHaveCount(2);
    expect($classes[0]->getEventName())->toBe('A');
});
