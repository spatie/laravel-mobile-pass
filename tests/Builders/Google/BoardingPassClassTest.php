<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\BoardingPassClass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('computes the full id from issuer + suffix', function () {
    expect(BoardingPassClass::make('flight-ba123')->id())->toBe('3388.flight-ba123');
});

it('saves the expected payload to Google', function () {
    Http::fake(['*/flightClass' => Http::response([], 200)]);

    BoardingPassClass::make('flight-ba123')
        ->setIssuerName('British Airways')
        ->setAirlineCode('BA')
        ->setFlightNumber('123')
        ->setOriginAirportCode('LHR')
        ->setDestinationAirportCode('BRU')
        ->setLogoUrl('https://cdn.example.com/ba.png')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['id'])->toBe('3388.flight-ba123');
        expect($request['issuerName'])->toBe('British Airways');
        expect($request['flightHeader']['carrier']['airlineCode'])->toBe('BA');
        expect($request['flightHeader']['flightNumber'])->toBe('123');
        expect($request['origin']['airportIataCode'])->toBe('LHR');
        expect($request['destination']['airportIataCode'])->toBe('BRU');
        expect($request['logo']['sourceUri']['uri'])->toBe('https://cdn.example.com/ba.png');

        return true;
    });
});

it('retire() patches reviewStatus to REJECTED', function () {
    Http::fake(['*/flightClass/3388.flight-ba123' => Http::response([], 200)]);

    BoardingPassClass::make('flight-ba123')->retire();

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request['reviewStatus'] === 'REJECTED'
    );
});

it('find() returns null on 404', function () {
    Http::fake(['*/flightClass/3388.missing' => Http::response([], 404)]);

    expect(BoardingPassClass::find('missing'))->toBeNull();
});

it('all() returns a collection hydrated from resources', function () {
    Http::fake(['*/flightClass*' => Http::response([
        'resources' => [
            ['id' => '3388.a', 'flightHeader' => ['carrier' => ['airlineCode' => 'BA'], 'flightNumber' => '100']],
            ['id' => '3388.b', 'flightHeader' => ['carrier' => ['airlineCode' => 'BA'], 'flightNumber' => '200']],
        ],
    ], 200)]);

    $classes = BoardingPassClass::all();

    expect($classes)->toHaveCount(2);
    expect($classes[0]->getFlightNumber())->toBe('100');
});
