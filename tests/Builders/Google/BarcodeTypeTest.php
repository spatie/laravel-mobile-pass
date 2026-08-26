<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('translates BarcodeType::Code39 to Google\'s CODE_39', function () {
    Http::fake(['*/genericObject' => Http::response([], 200)]);

    GenericPassBuilder::make()
        ->setClass('gen-2026')
        ->setObjectSuffix('alpha')
        ->setHeader('Member Card')
        ->setBarcode(BarcodeType::Code39, 'MEMBER-42')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['barcode']['type'])->toBe('CODE_39');

        return true;
    });
});

it('translates BarcodeType::Codabar to Google\'s CODABAR', function () {
    Http::fake(['*/genericObject' => Http::response([], 200)]);

    GenericPassBuilder::make()
        ->setClass('gen-2026')
        ->setObjectSuffix('alpha')
        ->setHeader('Member Card')
        ->setBarcode(BarcodeType::Codabar, 'MEMBER-42')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['barcode']['type'])->toBe('CODABAR');

        return true;
    });
});

it('translates BarcodeType::Ean13 to Google\'s EAN_13', function () {
    Http::fake(['*/genericObject' => Http::response([], 200)]);

    GenericPassBuilder::make()
        ->setClass('gen-2026')
        ->setObjectSuffix('alpha')
        ->setHeader('Member Card')
        ->setBarcode(BarcodeType::Ean13, 'MEMBER-42')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['barcode']['type'])->toBe('EAN_13');

        return true;
    });
});
