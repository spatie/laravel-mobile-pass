---
title: Adding barcodes
weight: 4
---

Barcodes are the scannable bit of a pass: a QR code at a festival gate, a PDF417 on a boarding pass, an Aztec for a bus ticket. Both Apple and Google Wallet understand the same four formats, and the package exposes them through a shared `BarcodeType` enum.

The four cases on `BarcodeType` are `Qr`, `Pdf417`, `Aztec`, and `Code128`. Pick the one your scanners expect.

## Apple

Every Apple builder accepts a barcode through `setBarcode()`. Pass the format and the encoded value:

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

EventTicketPassBuilder::make()
    ->setOrganisationName('Fab Four Promotions')
    ->setSerialNumber('BTL-SHEA-0042')
    ->setDescription('The Beatles at Shea Stadium')
    ->setBarcode(BarcodeType::Qr, 'TICKET-12345')
    ->save();
```

You can include a human-readable fallback rendered under the code with a third argument:

```php
$builder->setBarcode(
    BarcodeType::Qr,
    'TICKET-12345',
    altText: 'Show this at the gate',
);
```

Under the hood the builder writes the barcode into both `barcode` (for older iOS) and `barcodes` (for iOS 9+) so the pass renders everywhere.

## Google

Every Google builder accepts the same call shape:

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    ->setBarcode(BarcodeType::Qr, 'TICKET-12345')
    ->save();
```

The builder translates the `BarcodeType` case into Google's own format names (`QR_CODE`, `PDF_417`, `AZTEC`, `CODE_128`) when it builds the payload.
