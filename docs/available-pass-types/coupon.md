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
    ->setTitle('20% off for Dan')
    ->setRedemptionCode('DAN20SUMMER')
    ->save();
```
