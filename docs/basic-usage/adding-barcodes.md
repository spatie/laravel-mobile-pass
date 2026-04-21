---
title: Adding barcodes
weight: 4
---

Barcodes are the scannable bit of a pass: a QR code at a festival gate, a PDF417 on a boarding pass, an Aztec for a bus ticket. Both Apple and Google Wallet understand the same four formats, and the package exposes them through a shared `Barcode` entity.

## The Barcode entity

The `Barcode` class lives in the Apple namespace because the same entity drives both platforms. Each builder adapts it to the shape its wallet wants.

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

$barcode = Barcode::make(BarcodeType::QR, 'TICKET-12345');
```

The four cases on `BarcodeType` are `QR`, `PDF417`, `Aztec`, and `Code128`. Pick the one your scanners expect.

You can include a human-readable fallback under the code with `withAltText()`:

```php
Barcode::make(BarcodeType::QR, 'TICKET-12345')
    ->withAltText('Show this at the gate');
```

## Google

Every Google pass builder accepts a `Barcode`:

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;

EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    ->setBarcode(Barcode::make(BarcodeType::QR, 'TICKET-12345'))
    // ...
    ->save();
```

The builder translates the `BarcodeType` case into Google's own format names (`QR_CODE`, `PDF_417`, `AZTEC`, `CODE_128`) when it builds the payload.

## Apple

Apple barcodes aren't exposed through a dedicated setter on the builder yet. The underlying pass format supports them, and the `Barcode` entity is the one you'd hand in, but the public API for attaching it is still being worked on. If you need a barcode on an Apple pass today, reach for the raw `data` on the builder or reach out in an issue on the package repo.
