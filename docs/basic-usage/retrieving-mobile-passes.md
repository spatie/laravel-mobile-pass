---
title: Retrieving mobile passes
weight: 9
---

Once a pass is associated with one of your models (see [Generating your first pass](/docs/laravel-mobile-pass/v1/basic-usage/generating-your-first-pass) for how to wire that up), the `HasMobilePasses` trait gives you a few ways to fetch them back.

## Fetching Apple or Google passes

If you issue passes to both Apple Wallet and Google Wallet, the trait exposes dedicated helpers so you don't have to reach for `$pass->platform` yourself:

```php
$user = User::first();

$user->applePasses;   // only Apple passes
$user->googlePasses;  // only Google passes

$user->firstApplePass();
$user->firstGooglePass();
```

Both `firstApplePass` and `firstGooglePass` accept an optional `PassType` to narrow further:

```php
use Spatie\LaravelMobilePass\Enums\PassType;

$user->firstApplePass(PassType::EventTicket);
```

## Fetching all passes

If you don't care about the platform, grab every pass tied to the model:

```php
$mobilePasses = User::first()->mobilePasses;
```

For a single pass, use `firstMobilePass`:

```php
$mobilePass = User::first()->firstMobilePass();
```

The `firstMobilePass` method takes an optional `PassType` to scope to a specific kind of pass, an optional `Platform` to cover both axes at once, and a `filter` closure for anything more custom:

```php
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\Platform;

$couponPass = User::first()->firstMobilePass(PassType::Coupon);

$appleEventTicket = User::first()->firstMobilePass(PassType::EventTicket, Platform::Apple);

$customQuery = User::first()->firstMobilePass(filter: function ($query) {
    $query->where('type', PassType::Coupon);
});
```
