---
title: Apple-specific methods
weight: 2
---

Every Apple builder extends `ApplePassBuilder`, so each pass type ships with the same base API for fields, images, and colours. These methods are Apple-only. The basics of building a pass (creating a builder, setting an organisation name, calling `save()`) are covered in [Generating your first pass](/docs/laravel-mobile-pass/v1/basic-usage/generating-your-first-pass).

## Field zones

Apple splits the fields on a pass across five zones, and every Apple builder exposes one `add` method per zone:

```php
addHeaderField(string $key, string $value, ?string $label, ?string $changeMessage, ?DateType $dateStyle, ?TimeStyleType $timeStyle, ?bool $showDateAsRelative)
addField(string $key, string $value, FieldType $type = FieldType::Primary, ...)
addSecondaryField(string $key, string $value, ...)
addAuxiliaryField(string $key, string $value, ...)
addBackField(string $key, string $value, ...)
```

`$key` is a free-form identifier you pick. It's unique within the pass, and you'll reference it later when you want to update that field.

`$label` defaults to a title-cased version of the key. Pass a custom label when the key doesn't read nicely on the pass.

`$changeMessage` is the notification the user's device shows when the value of this field changes. Use `:value` as a placeholder for the new value (for example, `'Your gate has changed to :value'`).

`$dateStyle` and `$timeStyle` let you format a value Apple recognises as a date. Pass a `DateType` (none, short, medium, long, or full) and/or a `TimeStyleType` case. Combine with `$showDateAsRelative: true` to render the date as "in 2 hours" rather than an absolute timestamp.

## Images

Every pass takes a logo and an icon. Pass a `Spatie\LaravelMobilePass\Builders\Apple\Entities\Image` entity built with the path(s) to your files:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;

$builder
    ->setLogoImage(Image::make(x1Path: public_path('images/logo.png')))
    ->setIconImage(Image::make(x1Path: public_path('images/icon.png')));
```

Apple expects images at 1x, 2x, and 3x resolutions. If you have all three, pass `x2Path` and `x3Path` as well. Boarding passes also expose `setFooterImage()`.

## Colours

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Colour;

$builder
    ->setBackgroundColour(Colour::makeFromHex('#1d72b8'))
    ->setForegroundColour(Colour::makeFromHex('#ffffff'))
    ->setLabelColour(Colour::makeFromHex('#a7c7e7'));
```

Background, foreground, and label colours are all optional. Apple picks sensible defaults if you skip them.

## Download name

The file Apple Wallet downloads defaults to the pass's serial number. Pass a friendlier name with `setDownloadName('Beatles-Shea-Ticket')` and that's what the user sees.

## Niche methods

- `setWifiDetails(WifiNetwork ...$wifiNetwork)`: attach one or more Wi-Fi credentials so the pass can join a network when scanned.
- `setTotalPrice(Price $totalPrice)`: record the total cost on the pass (useful for event tickets and coupons).
