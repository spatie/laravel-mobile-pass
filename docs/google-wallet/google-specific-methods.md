---
title: Google-specific methods
weight: 3
---

Every Google builder extends `GooglePassBuilder`, which handles the bits of a pass that are Object-specific. Look-and-feel (logos, colours, event name, venue) lives on the Class, covered in [Declaring Google pass classes](declaring-google-pass-classes). The basics of building a pass are covered in [Generating your first pass](/docs/laravel-mobile-pass/v1/basic-usage/generating-your-first-pass).

## Referencing the Class

Every Google pass Object has to point at a Class. Call `setClass()` with the suffix you used when declaring it:

```php
EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    // ...
    ->save();
```

Saving without a class throws a `RuntimeException`. The full class ID Google sees is `{issuer-id}.{suffix}`, which the package stitches together for you.

## Object IDs

Each Google pass Object also has its own unique ID. By default, the package generates a UUID for each Object you create. If you'd rather control the ID yourself (say, to line it up with a primary key from your database), pass a suffix:

```php
$builder->setObjectSuffix("ticket-{$ticketId}");
```

## Barcodes

Pass a `Barcode` entity to render a barcode on the Google pass:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

$builder->setBarcode(Barcode::make(BarcodeType::QR, 'TICKET-12345'));
```

The `Barcode` entity lives in the `Apple\Entities` namespace because the same entity drives both platforms. The Google builder translates it into Google's barcode payload shape (`QR_CODE`, `PDF_417`, `AZTEC`, `CODE_128`) for you.
