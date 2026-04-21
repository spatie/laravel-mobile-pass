---
title: Associating passes with models
weight: 5
---

You can link mobile passes to any of your models. That's useful when you want to tie a pass to a user, a product, an order, or anything else you need to look up later.

## Preparing your model

Any model can hold mobile passes. Start by adding the `HasMobilePasses` trait to it:

```php
use Spatie\LaravelMobilePass\Models\Concerns\HasMobilePasses;

class User extends Model
{
    use HasMobilePasses;
}
```

## Associating a mobile pass with a model

With the trait in place, you can associate a pass with the model:

```php
$mobilePassModel = AirlinePassBuilder::make()
    ->setOrganisationName('My organisation')
    // other methods
    ->save();

User::first()->addMobilePass($mobilePassModel);
```

## Retrieving associated mobile passes

You can grab every mobile pass tied to a model:

```php
$mobilePasses = User::first()->mobilePasses;
```

There's also a shortcut for the first one:

```php
$mobilePass = User::first()->firstMobilePass();
```

The `firstMobilePass` method takes an optional parameter to scope down to a specific type:

```php
use Spatie\LaravelMobilePass\Enums\PassType;

$couponPass = User::first()->firstMobilePass(PassType::Coupon);
```

There's also a `filter` parameter that accepts a closure if you want to shape the query yourself:

```php
$couponPass = User::first()->firstMobilePass(filter: function ($query) {
    $query->where('type', PassType::Coupon);
});
```

## Filtering by platform

Most users carry passes on just one platform, but if you issue to both Apple Wallet and Google Wallet, the trait gives you dedicated helpers so you don't have to reach for `$pass->platform` yourself:

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

If you need both axes at once, `firstMobilePass` also takes a `Platform`:

```php
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\Platform;

$user->firstMobilePass(PassType::EventTicket, Platform::Apple);
```
