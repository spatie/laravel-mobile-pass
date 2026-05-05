<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('creates a MobilePass row and POSTs the loyalty object to Google', function () {
    Http::fake(['*/loyaltyObject' => Http::response([], 200)]);

    $pass = LoyaltyPassBuilder::make()
        ->setClass('lp-2026')
        ->setObjectSuffix('jane')
        ->setAccountId('AC-42')
        ->setAccountName('Jane Doe')
        ->setBalanceMicros(125000000)
        ->setBalanceString('125 points')
        ->setBarcode(BarcodeType::Aztec, 'LP-42')
        ->save();

    expect($pass->platform)->toBe(Platform::Google);
    expect($pass->content['googleObjectId'])->toBe('3388.jane');
    expect($pass->content['googleClassId'])->toBe('3388.lp-2026');
    expect($pass->content['googleClassType'])->toBe('loyaltyClass');

    Http::assertSent(function ($request) {
        expect($request['accountId'])->toBe('AC-42');
        expect($request['accountName'])->toBe('Jane Doe');
        expect($request['loyaltyPoints']['balance']['micros'])->toBe(125000000);
        expect($request['loyaltyPoints']['balance']['string'])->toBe('125 points');
        expect($request['barcode']['type'])->toBe('AZTEC');

        return true;
    });
});

it('throws when setClass() is not called', function () {
    LoyaltyPassBuilder::make()->setAccountId('AC-42')->save();
})->throws(RuntimeException::class);
