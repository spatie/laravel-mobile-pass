<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassClass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('computes the full id from issuer + suffix', function () {
    expect(LoyaltyPassClass::make('spatie-club')->id())->toBe('3388.spatie-club');
});

it('saves the expected payload to Google', function () {
    Http::fake(['*/loyaltyClass' => Http::response([], 200)]);

    LoyaltyPassClass::make('spatie-club')
        ->setIssuerName('Spatie')
        ->setProgramName('Spatie Club')
        ->setProgramLogoUrl('https://cdn.example.com/logo.png')
        ->setRewardsTier('Gold')
        ->setRewardsTierLabel('Tier')
        ->setAccountNameLabel('Member')
        ->setAccountIdLabel('Membership ID')
        ->setBackgroundColor('#000000')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['id'])->toBe('3388.spatie-club');
        expect($request['issuerName'])->toBe('Spatie');
        expect($request['programName'])->toBe('Spatie Club');
        expect($request['programLogo']['sourceUri']['uri'])->toBe('https://cdn.example.com/logo.png');
        expect($request['rewardsTier'])->toBe('Gold');
        expect($request['rewardsTierLabel'])->toBe('Tier');
        expect($request['accountNameLabel'])->toBe('Member');
        expect($request['accountIdLabel'])->toBe('Membership ID');
        expect($request['hexBackgroundColor'])->toBe('#000000');

        return true;
    });
});

it('retire() patches reviewStatus to REJECTED', function () {
    Http::fake(['*/loyaltyClass/3388.spatie-club' => Http::response([], 200)]);

    LoyaltyPassClass::make('spatie-club')->retire();

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request['reviewStatus'] === 'REJECTED'
    );
});

it('find() returns null on 404', function () {
    Http::fake(['*/loyaltyClass/3388.missing' => Http::response([], 404)]);

    expect(LoyaltyPassClass::find('missing'))->toBeNull();
});

it('all() returns a collection hydrated from resources', function () {
    Http::fake(['*/loyaltyClass*' => Http::response([
        'resources' => [
            ['id' => '3388.a', 'programName' => 'Club A'],
            ['id' => '3388.b', 'programName' => 'Club B'],
        ],
    ], 200)]);

    $classes = LoyaltyPassClass::all();

    expect($classes)->toHaveCount(2);
    expect($classes[0]->getProgramName())->toBe('Club A');
});
