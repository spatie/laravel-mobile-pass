<?php

use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('appends barcodes in call order via addBarcode', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->addBarcode(BarcodeType::Ean13, '4006381333931')
        ->addBarcode(BarcodeType::Qr, 'TICKET-12345')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['barcodes'])->toHaveCount(2);
    expect($data['barcodes'][0])->toMatchArray([
        'format' => 'PKBarcodeFormatEAN13',
        'message' => '4006381333931',
    ]);
    expect($data['barcodes'][1])->toMatchArray([
        'format' => 'PKBarcodeFormatQR',
        'message' => 'TICKET-12345',
    ]);
});

it('populates the deprecated barcode key with the last entry', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->addBarcode(BarcodeType::Ean13, '4006381333931')
        ->addBarcode(BarcodeType::Qr, 'TICKET-12345')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['barcode'])->toMatchArray([
        'format' => 'PKBarcodeFormatQR',
        'message' => 'TICKET-12345',
    ]);
});

it('resets to a single entry when setBarcode is called after addBarcode', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->addBarcode(BarcodeType::Ean13, '4006381333931')
        ->addBarcode(BarcodeType::Qr, 'TICKET-12345')
        ->setBarcode(BarcodeType::Aztec, 'TICKET-99999')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['barcodes'])->toHaveCount(1);
    expect($data['barcodes'][0])->toMatchArray([
        'format' => 'PKBarcodeFormatAztec',
        'message' => 'TICKET-99999',
    ]);
    expect($data['barcode'])->toMatchArray([
        'format' => 'PKBarcodeFormatAztec',
        'message' => 'TICKET-99999',
    ]);
});

it('behaves like setBarcode when addBarcode is called with nothing set before it', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->addBarcode(BarcodeType::Qr, 'TICKET-12345')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data['barcodes'])->toHaveCount(1);
    expect($data['barcode'])->toMatchArray([
        'format' => 'PKBarcodeFormatQR',
        'message' => 'TICKET-12345',
    ]);
});

it('round-trips multiple barcodes through save and hydrate, preserving order', function () {
    $model = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->addBarcode(BarcodeType::Ean13, '4006381333931')
        ->addBarcode(BarcodeType::Qr, 'TICKET-12345')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['barcodes'])->toHaveCount(2);
    expect($data['barcodes'][0]['format'])->toBe('PKBarcodeFormatEAN13');
    expect($data['barcodes'][1]['format'])->toBe('PKBarcodeFormatQR');
});

it('hydrates a legacy row with only a singular barcode key into a one-element list', function () {
    $model = MobilePass::factory()->make([
        'builder_name' => EventTicketPassBuilder::name(),
        'content' => [
            'organizationName' => 'Fab Four Promotions',
            'serialNumber' => 'BTL-SHEA-0042',
            'description' => 'The Beatles at Shea Stadium',
            'barcode' => [
                'format' => 'PKBarcodeFormatQR',
                'message' => 'TICKET-12345',
                'messageEncoding' => 'iso-8859-1',
            ],
        ],
    ]);

    $data = EventTicketPassBuilder::hydrate($model)->data();

    expect($data['barcodes'])->toHaveCount(1);
    expect($data['barcodes'][0]['format'])->toBe('PKBarcodeFormatQR');
    expect($data['barcode']['format'])->toBe('PKBarcodeFormatQR');
});

it('omits barcode and barcodes when none are set', function () {
    $data = EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        ->setSerialNumber('BTL-SHEA-0042')
        ->setDescription('The Beatles at Shea Stadium')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($data)->not->toHaveKey('barcode');
    expect($data)->not->toHaveKey('barcodes');
});
