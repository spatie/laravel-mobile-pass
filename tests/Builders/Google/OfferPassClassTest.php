<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\OfferPassClass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('computes the full id from issuer + suffix', function () {
    expect(OfferPassClass::make('summer-sale')->id())->toBe('3388.summer-sale');
});

it('saves the expected payload to Google', function () {
    Http::fake(['*/offerClass' => Http::response([], 200)]);

    OfferPassClass::make('summer-sale')
        ->setIssuerName('Spatie')
        ->setTitle('Summer Sale')
        ->setRedemptionChannel('ONLINE')
        ->setProvider('Spatie Shop')
        ->setDetails('50% off all packages')
        ->setFinePrint('Not combinable with other offers')
        ->setLogoUrl('https://cdn.example.com/logo.png')
        ->setBackgroundColor('#ff0000')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['id'])->toBe('3388.summer-sale');
        expect($request['issuerName'])->toBe('Spatie');
        expect($request['title'])->toBe('Summer Sale');
        expect($request['redemptionChannel'])->toBe('ONLINE');
        expect($request['provider'])->toBe('Spatie Shop');
        expect($request['details'])->toBe('50% off all packages');
        expect($request['finePrint'])->toBe('Not combinable with other offers');
        expect($request['logo']['sourceUri']['uri'])->toBe('https://cdn.example.com/logo.png');
        expect($request['hexBackgroundColor'])->toBe('#ff0000');

        return true;
    });
});

it('retire() patches reviewStatus to REJECTED', function () {
    Http::fake(['*/offerClass/3388.summer-sale' => Http::response([], 200)]);

    OfferPassClass::make('summer-sale')->retire();

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request['reviewStatus'] === 'REJECTED'
    );
});

it('find() returns null on 404', function () {
    Http::fake(['*/offerClass/3388.missing' => Http::response([], 404)]);

    expect(OfferPassClass::find('missing'))->toBeNull();
});

it('all() returns a collection hydrated from resources', function () {
    Http::fake(['*/offerClass*' => Http::response([
        'resources' => [
            ['id' => '3388.a', 'title' => 'Offer A'],
            ['id' => '3388.b', 'title' => 'Offer B'],
        ],
    ], 200)]);

    $classes = OfferPassClass::all();

    expect($classes)->toHaveCount(2);
    expect($classes[0]->getTitle())->toBe('Offer A');
});
