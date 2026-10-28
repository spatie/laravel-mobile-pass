<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassClass;
use Spatie\LaravelMobilePass\Exceptions\PlatformDoesntSupport;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('computes the full id from issuer + suffix', function () {
    expect(GenericPassClass::make('membership')->id())->toBe('3388.membership');
});

it('only sends fields that exist on genericClass', function () {
    Http::fake(['*/genericClass' => Http::response([], 200)]);

    GenericPassClass::make('membership')
        ->addLink('https://spatie.be', 'Visit our site')
        ->addTextModule('Opening hours', 'Mon to Fri, 9 to 5')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['id'])->toBe('3388.membership');
        expect($request['linksModuleData']['uris'][0]['uri'])->toBe('https://spatie.be');
        expect($request['textModulesData'][0]['header'])->toBe('Opening hours');

        return true;
    });
});

it('does not send object-level display fields, locations or reviewStatus on genericClass', function () {
    Http::fake(['*/genericClass' => Http::response([], 200)]);

    GenericPassClass::make('membership')
        ->setIssuerName('Spatie')
        ->setCardTitle('Spatie Membership')
        ->setSubheader('Annual Plan')
        ->setHeader('Pro Member')
        ->setBackgroundColor('#00ff00')
        ->setLogoUrl('https://cdn.example.com/logo.png')
        ->setHeroImageUrl('https://cdn.example.com/hero.png')
        ->addLocation(37.424015, -122.09259)
        ->save();

    Http::assertSent(function ($request) {
        expect($request->data())->toBe(['id' => '3388.membership']);

        return true;
    });
});

it('keeps the deprecated class-level display setters readable', function () {
    $class = GenericPassClass::make('membership')->setCardTitle('Spatie Membership');

    expect($class->getCardTitle())->toBe('Spatie Membership');
});

it('replaces an existing generic class with a full PUT and no reviewStatus', function () {
    Http::fakeSequence()
        ->push(['error' => ['code' => 409]], 409)
        ->push(['id' => '3388.membership'], 200);

    GenericPassClass::make('membership')
        ->addTextModule('Opening hours', 'Mon to Fri, 9 to 5')
        ->save();

    Http::assertSentCount(2);
    Http::assertSent(function ($request) {
        return $request->method() === 'PUT'
            && str_ends_with($request->url(), '/genericClass/3388.membership')
            && ! array_key_exists('reviewStatus', $request->data())
            && $request['textModulesData'][0]['header'] === 'Opening hours';
    });
});

it('retire() is unsupported and sends nothing to Google', function () {
    Http::fake();

    expect(fn () => GenericPassClass::make('membership')->retire())
        ->toThrow(PlatformDoesntSupport::class);

    Http::assertNothingSent();
});

it('find() returns null on 404', function () {
    Http::fake(['*/genericClass/3388.missing' => Http::response([], 404)]);

    expect(GenericPassClass::find('missing'))->toBeNull();
});

it('all() returns a collection hydrated from resources', function () {
    Http::fake(['*/genericClass*' => Http::response([
        'resources' => [
            ['id' => '3388.a', 'cardTitle' => ['defaultValue' => ['language' => 'en-US', 'value' => 'A']]],
            ['id' => '3388.b', 'cardTitle' => ['defaultValue' => ['language' => 'en-US', 'value' => 'B']]],
        ],
    ], 200)]);

    $classes = GenericPassClass::all();

    expect($classes)->toHaveCount(2);
    expect($classes[0]->getCardTitle())->toBe('A');
});
