<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\OfferPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('creates a MobilePass row and POSTs the offer object to Google', function () {
    Http::fake(['*/offerObject' => Http::response([], 200)]);

    $pass = OfferPassBuilder::make()
        ->setClass('promo-2026')
        ->setObjectSuffix('spring')
        ->setTitle('Spring Sale 20%')
        ->setRedemptionCode('SPRING20')
        ->setBarcode(Barcode::make(BarcodeType::Code128, 'SPRING20'))
        ->save();

    expect($pass->platform)->toBe(Platform::Google);
    expect($pass->content['googleObjectId'])->toBe('3388.spring');
    expect($pass->content['googleClassId'])->toBe('3388.promo-2026');
    expect($pass->content['googleClassType'])->toBe('offerClass');

    Http::assertSent(function ($request) {
        expect($request['title'])->toBe('Spring Sale 20%');
        expect($request['redemptionCode'])->toBe('SPRING20');
        expect($request['barcode']['type'])->toBe('CODE_128');
        expect($request['barcode']['value'])->toBe('SPRING20');

        return true;
    });
});

it('throws when setClass() is not called', function () {
    OfferPassBuilder::make()->setTitle('Sale')->save();
})->throws(RuntimeException::class);
