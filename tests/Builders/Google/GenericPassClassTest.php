<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassClass;
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

it('saves the expected payload to Google', function () {
    Http::fake(['*/genericClass' => Http::response([], 200)]);

    GenericPassClass::make('membership')
        ->setIssuerName('Spatie')
        ->setCardTitle('Spatie Membership')
        ->setSubheader('Annual Plan')
        ->setHeader('Pro Member')
        ->setBackgroundColor('#00ff00')
        ->setLogoUrl('https://cdn.example.com/logo.png')
        ->setHeroImageUrl('https://cdn.example.com/hero.png')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['id'])->toBe('3388.membership');
        expect($request['issuerName'])->toBe('Spatie');
        expect($request['cardTitle']['defaultValue']['value'])->toBe('Spatie Membership');
        expect($request['subheader']['defaultValue']['value'])->toBe('Annual Plan');
        expect($request['header']['defaultValue']['value'])->toBe('Pro Member');
        expect($request['hexBackgroundColor'])->toBe('#00ff00');
        expect($request['logo']['sourceUri']['uri'])->toBe('https://cdn.example.com/logo.png');
        expect($request['heroImage']['sourceUri']['uri'])->toBe('https://cdn.example.com/hero.png');
        expect($request['reviewStatus'])->toBe('UNDER_REVIEW');

        return true;
    });
});

it('resets reviewStatus to UNDER_REVIEW when updating an existing class', function () {
    Http::fakeSequence()
        ->push([
            'id' => '3388.membership',
            'reviewStatus' => 'APPROVED',
            'cardTitle' => [
                'defaultValue' => [
                    'language' => 'en-US',
                    'value' => 'Spatie Membership',
                ],
            ],
        ], 200)
        ->push(['error' => ['code' => 409]], 409)
        ->push(['id' => '3388.membership', 'reviewStatus' => 'UNDER_REVIEW'], 200);

    $class = GenericPassClass::find('membership');

    expect($class)->not->toBeNull();

    $class
        ->setIssuerName('Spatie')
        ->save();

    Http::assertSentCount(3);
    Http::assertSent(function ($request) {
        return $request->method() === 'PATCH'
            && str_ends_with($request->url(), '/genericClass/3388.membership')
            && $request['reviewStatus'] === 'UNDER_REVIEW';
    });
});

it('retire() patches reviewStatus to REJECTED', function () {
    Http::fake(['*/genericClass/3388.membership' => Http::response([], 200)]);

    GenericPassClass::make('membership')->retire();

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request['reviewStatus'] === 'REJECTED'
    );
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
