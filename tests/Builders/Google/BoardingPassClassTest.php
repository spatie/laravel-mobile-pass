<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\BoardingPassClass;
use Spatie\LaravelMobilePass\Exceptions\PlatformDoesntSupport;
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
        ->setLocalScheduledDepartureDateTime(Carbon::parse('2027-03-05 06:30:00', 'Europe/London'))
        ->setLogoUrl('https://cdn.example.com/ba.png')
        ->setHeroImageUrl('https://cdn.example.com/hero.png')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['id'])->toBe('3388.flight-ba123');
        expect($request['issuerName'])->toBe('British Airways');
        expect($request['flightHeader']['carrier']['carrierIataCode'])->toBe('BA');
        expect($request['flightHeader']['carrier'])->not->toHaveKey('airlineCode');
        expect($request['flightHeader']['carrier']['airlineLogo']['sourceUri']['uri'])->toBe('https://cdn.example.com/ba.png');
        expect($request['flightHeader']['flightNumber'])->toBe('123');
        expect($request['origin']['airportIataCode'])->toBe('LHR');
        expect($request['destination']['airportIataCode'])->toBe('BRU');
        expect($request['localScheduledDepartureDateTime'])->toBe('2027-03-05T06:30:00');
        expect($request['heroImage']['sourceUri']['uri'])->toBe('https://cdn.example.com/hero.png');
        expect($request->data())->not->toHaveKey('logo');

        return true;
    });
});

it('sends the local departure time without a UTC offset regardless of timezone', function () {
    Http::fake(['*/flightClass' => Http::response([], 200)]);

    BoardingPassClass::make('flight-ba123')
        ->setLocalScheduledDepartureDateTime(Carbon::parse('2027-03-05 06:30:00', 'America/New_York'))
        ->save();

    Http::assertSent(fn ($request) => $request['localScheduledDepartureDateTime'] === '2027-03-05T06:30:00');
});

it('hydrates the carrier from carrierIataCode and airlineLogo', function () {
    Http::fake(['*/flightClass/3388.flight-ba123' => Http::response([
        'id' => '3388.flight-ba123',
        'localScheduledDepartureDateTime' => '2027-03-05T06:30:00',
        'flightHeader' => [
            'carrier' => [
                'carrierIataCode' => 'BA',
                'airlineLogo' => ['sourceUri' => ['uri' => 'https://cdn.example.com/ba.png']],
            ],
            'flightNumber' => '123',
        ],
    ], 200)]);

    $class = BoardingPassClass::find('flight-ba123');

    expect($class->getAirlineCode())->toBe('BA');
    expect($class->getFlightNumber())->toBe('123');

    Http::fake(['*/flightClass' => Http::response([], 200)]);

    $class->save();

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request['flightHeader']['carrier']['carrierIataCode'] === 'BA'
        && $request['flightHeader']['carrier']['airlineLogo']['sourceUri']['uri'] === 'https://cdn.example.com/ba.png'
        && $request['localScheduledDepartureDateTime'] === '2027-03-05T06:30:00'
    );
});

it('hydrates legacy airlineCode and top-level logo from previously stored data', function () {
    Http::fake(['*/flightClass/3388.flight-ba123' => Http::response([
        'id' => '3388.flight-ba123',
        'logo' => ['sourceUri' => ['uri' => 'https://cdn.example.com/ba.png']],
        'flightHeader' => ['carrier' => ['airlineCode' => 'BA'], 'flightNumber' => '123'],
    ], 200)]);

    $class = BoardingPassClass::find('flight-ba123');

    expect($class->getAirlineCode())->toBe('BA');

    Http::fake(['*/flightClass' => Http::response([], 200)]);

    $class->save();

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request['flightHeader']['carrier'] === [
            'carrierIataCode' => 'BA',
            'airlineLogo' => ['sourceUri' => ['uri' => 'https://cdn.example.com/ba.png']],
        ]
        && ! array_key_exists('logo', $request->data())
    );
});

it('retire() is unsupported and sends nothing to Google', function () {
    Http::fake();

    expect(fn () => BoardingPassClass::make('flight-ba123')->retire())
        ->toThrow(PlatformDoesntSupport::class);

    Http::assertNothingSent();
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
