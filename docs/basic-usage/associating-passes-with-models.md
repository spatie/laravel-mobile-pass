---
title: Associating passes with models
weight: 2
---

This package offers methods to associate mobile passes with models. This can be useful if you want to associate a mobile pass with a user, a product, or any other model.

## Preparing your model

You can associate a mobile pass with any model. First you need to add the `HasMobilePasses` trait to the model:

```php
use Spatie\LaravelMobilePass\HasMobilePasses;

class User extends Model
{
    use HasMobilePasses;
}
```

## Associating a mobile pass with a model

Then you can associate a mobile pass with the model:

```php
$mobilePassModel = AirlinePassBuilder::make()
    ->setOrganisationName('My organisation')
    // other methods
    ->save();

User::first()->addMobilePass($mobilePassModel);
```

## Retrieving associated mobile passes

You can retrieve all mobile passes associated with a model:

```php
$mobilePasses = User::first()->mobilePasses;
```

There's also a convenience method to retrieve the first mobile pass associated with a model:

```php
$mobilePass = User::first()->firstMobilePass();
```

The `firstMobilePass` accept a parameter to retrieve the first mobile pass of a specific type:

```php
use Spatie\LaravelMobilePass\Enums\PassType::Coupon;

$couponPass = User::first()->firstMobilePass(PassType::Coupon);
```

There's also a parameter `filter` that accepts a closure to modify the query:

```php
$couponPass = User::first()->firstMobilePass(filter: function ($query) {
    $query->where('type', PassType::Coupon->value);
});
```

