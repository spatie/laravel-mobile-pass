<?php

use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;

it('sets a Wi-Fi QR barcode from ssid and password', function () {
    $data = GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setSerialNumber('WIFI-001')
        ->setDescription('Guest Wi-Fi')
        ->setWifiBarcode('Spatie Guest', 'welcome')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['barcode'])->toMatchArray([
        'format' => 'PKBarcodeFormatQR',
        'message' => 'WIFI:S:Spatie Guest;T:WPA;P:welcome;;',
        'altText' => 'Spatie Guest',
    ]);
});

it('builds a nopass Wi-Fi barcode when no password is given', function () {
    $data = GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setSerialNumber('WIFI-002')
        ->setDescription('Open Wi-Fi')
        ->setWifiBarcode('Open Network')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['barcode']['message'])->toBe('WIFI:S:Open Network;T:nopass;;');
});

it('uses a custom altText when provided', function () {
    $data = GenericPassBuilder::make()
        ->setOrganizationName('Spatie')
        ->setSerialNumber('WIFI-003')
        ->setDescription('Guest Wi-Fi')
        ->setWifiBarcode('Spatie Guest', 'welcome', altText: 'Scan to join')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['barcode']['altText'])->toBe('Scan to join');
});
