---
title: Coupon
weight: 4
---

Coupons cover discount codes, limited-time offers, and anything else with "redeem me" energy. Apple calls them coupons (`CouponPassBuilder`); Google calls them offers (`OfferPassBuilder`).

## Apple

```php
use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;

CouponPassBuilder::make()
    ->setOrganizationName('Spatie Store')
    ->setSerialNumber('COUPON-SUMMER25')
    ->setDescription('20% off everything this summer')
    ->addField('offer', '20%', label: 'Save')
    ->addSecondaryField('expires', '2026-08-31')
    ->save();
```

## Google

Declare the Class once per offer (the title, the fine print, the visuals), then create an Object per redeemer.

```php
use Spatie\LaravelMobilePass\Builders\Google\OfferPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\OfferPassClass;

// Once, per offer
OfferPassClass::make('summer-2026-20off')
    ->setIssuerName('Spatie Store')
    ->setTitle('Save 20% this summer')
    ->setProvider('Spatie')
    ->setRedemptionChannel('ONLINE')
    ->setFinePrint('One use per customer. Expires 2026-08-31.')
    ->setLogoUrl('https://cdn.example.com/spatie-logo.png')
    ->save();

// Per redeemer
OfferPassBuilder::make()
    ->setClass('summer-2026-20off')
    ->setRedemptionCode('DAN20SUMMER')
    ->save();
```

The offer's title lives on the Class (`OfferPassClass::setTitle()`). Google's offer Object has no title, so `OfferPassBuilder::setTitle()` is deprecated and its value is not sent.

Google's offer Object has no redemption code field either, so `setRedemptionCode()` puts the code where people can see it:

- If you haven't called `setBarcode()`, the code becomes a QR barcode, with the code printed underneath as readable text.
- If you have set a barcode with a different value, the code is added as a "Redemption code" text module on the pass.
- If your barcode already carries the same value, nothing extra is added.
