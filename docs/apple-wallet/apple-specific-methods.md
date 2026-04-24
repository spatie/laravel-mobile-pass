---
title: Apple-specific methods
weight: 2
---

A handful of Apple-only setters that don't earn their own page. Each one is a small customisation on top of the essentials covered in [Generating your first pass](basic-usage/generating-your-first-pass).

## Colors

Background color is covered in [Adding images](basic-usage/adding-images). For foreground and label colors pass a hex string:

```php
$builder
    ->setForegroundColor('#ffffff')
    ->setLabelColor('#a7c7e7');
```

Both are optional. Apple picks sensible defaults if you skip them.

## Download name

The file Apple Wallet downloads defaults to the pass's serial number. Pass a friendlier name with `setDownloadName('Beatles-Shea-Ticket')` and that's what the user sees.

## Total price

Record a monetary value on the pass with `setTotalPrice()`. Useful on event tickets and coupons where the price is part of the pass's identity.

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Price;

$builder->setTotalPrice(Price::make(amount: '49.50', currencyCode: 'USD'));
```

The price lands on the pass's `semantics.totalPrice`, so Apple can surface it consistently in Wallet and Mail previews.
