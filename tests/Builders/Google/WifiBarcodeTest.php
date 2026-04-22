<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('sets a Wi-Fi QR barcode from ssid and password', function () {
    Http::fake(['*/genericObject' => Http::response([], 200)]);

    GenericPassBuilder::make()
        ->setClass('gen-2026')
        ->setObjectSuffix('wifi')
        ->setHeader('Guest Wi-Fi')
        ->setWifiBarcode('Spatie Guest', 'welcome')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['barcode']['type'])->toBe('QR_CODE');
        expect($request['barcode']['value'])->toBe('WIFI:S:Spatie Guest;T:WPA;P:welcome;;');
        expect($request['barcode']['alternateText'])->toBe('Spatie Guest');

        return true;
    });
});
