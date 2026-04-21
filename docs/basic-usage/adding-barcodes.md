---
title: Adding barcodes
weight: 4
---

Barcodes are the scannable bit of a pass: a QR code at a festival gate, a PDF417 on a boarding pass, an Aztec for a bus ticket. Both Apple and Google Wallet understand the same four formats, and the package exposes them through a shared `BarcodeType` enum.

The four cases on `BarcodeType` are `Qr`, `Pdf417`, `Aztec`, and `Code128`. Pick the one your scanners expect.

## Apple

Apple barcodes aren't exposed through a dedicated setter on the builder yet. The underlying pass format supports them, and the `Barcode` entity is the one you'd hand in, but the public API for attaching it is still being worked on. If you need a barcode on an Apple pass today, reach for the raw `data` on the builder or reach out in an issue on the package repo.

## Google

Every Google pass builder accepts a barcode. Pass the format and the encoded value:

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    ->setBarcode(BarcodeType::Qr, 'TICKET-12345')
    // ...
    ->save();
```

You can include a human-readable fallback under the code with a third argument:

```php
$builder->setBarcode(BarcodeType::Qr, 'TICKET-12345', altText: 'Show this at the gate');
```

The builder translates the `BarcodeType` case into Google's own format names (`QR_CODE`, `PDF_417`, `AZTEC`, `CODE_128`) when it builds the payload.
