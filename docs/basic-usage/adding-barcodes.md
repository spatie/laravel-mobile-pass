---
title: Adding barcodes
weight: 4
---

Barcodes are the scannable bit of a pass: a QR code at a festival gate, a PDF417 on a boarding pass, an Aztec for a bus ticket. The package exposes barcode formats through a shared `BarcodeType` enum, restricted to the formats both Apple and Google Wallet understand.

The seven cases on `BarcodeType` are `Qr`, `Pdf417`, `Aztec`, `Code128`, `Code39`, `Codabar`, and `Ean13`. Pick the one your scanners expect.

Apple's Wallet Passes reference also documents an eighth format, `PKBarcodeFormatI2of5` (generic Interleaved 2 of 5) — it's deliberately left out of `BarcodeType`. Google Wallet's closest equivalent, `ITF_14`, is a narrower 14-digit GTIN variant, not the same symbology, so there's no faithful cross-platform mapping for it. Since `BarcodeType` exists specifically so the same call works on both platforms, and any scanner setup choosing a barcode format should already be picking one both platforms render correctly, this isn't expected to be a real limitation in practice.

## Apple

Every Apple builder accepts a barcode through `setBarcode()`. Pass the format and the encoded value:

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

EventTicketPassBuilder::make()
    ->setOrganizationName('Fab Four Promotions')
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

Under the hood the builder writes into both `barcode` (deprecated, read by iOS 8 and earlier) and `barcodes` (the list every current version of Wallet reads).

That list is ordered. Wallet renders the first format the device supports and ignores the rest, so you can offer a preferred format with a fallback behind it. Use `addBarcode()` to append instead of replace:

```php
$builder
    ->addBarcode(BarcodeType::Pdf417, 'TICKET-12345')
    ->addBarcode(BarcodeType::Qr, 'TICKET-12345');
```

Devices that can render PDF417 use it, the rest fall back to the QR code.

Calling `setBarcode()` still replaces the whole list with one barcode, exactly as before, so passes built with a single call are unaffected. The deprecated singular `barcode` key gets the last entry in the list, on the assumption that you ordered it preferred first and most broadly compatible last.

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

The builder translates the `BarcodeType` case into Google's own format names (`QR_CODE`, `PDF_417`, `AZTEC`, `CODE_128`, `CODE_39`, `CODABAR`, `EAN_13`) when it builds the payload.
