<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('creates a MobilePass row and POSTs the generic object to Google', function () {
    Http::fake(['*/genericObject' => Http::response([], 200)]);

    $pass = GenericPassBuilder::make()
        ->setClass('gen-2026')
        ->setObjectSuffix('alpha')
        ->setHeader('Member Card')
        ->setCardTitle('Acme Inc')
        ->setSubheader('Gold Tier')
        ->setExpiryNotificationEnabled(true)
        ->setBarcode(Barcode::make(BarcodeType::QR, 'MEMBER-42'))
        ->save();

    expect($pass->platform)->toBe(Platform::Google);
    expect($pass->content['googleObjectId'])->toBe('3388.alpha');
    expect($pass->content['googleClassId'])->toBe('3388.gen-2026');
    expect($pass->content['googleClassType'])->toBe('genericClass');

    Http::assertSent(function ($request) {
        expect($request['header']['defaultValue']['value'])->toBe('Member Card');
        expect($request['cardTitle']['defaultValue']['value'])->toBe('Acme Inc');
        expect($request['subheader']['defaultValue']['value'])->toBe('Gold Tier');
        expect($request['notifications']['expiryNotification']['enableNotification'])->toBeTrue();
        expect($request['barcode']['type'])->toBe('QR_CODE');

        return true;
    });
});

it('throws when setClass() is not called', function () {
    GenericPassBuilder::make()->setHeader('x')->save();
})->throws(RuntimeException::class);
