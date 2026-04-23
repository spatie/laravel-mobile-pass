---
title: Object methods
weight: 2
---

Every Google builder extends `GooglePassBuilder`, which handles the bits of a pass that are specific to one user. Look-and-feel (logos, colors, event name, venue) lives on the Class, covered in [Pass classes](/docs/laravel-mobile-pass/v1/google-wallet/pass-classes).

## Referencing the Class

Every Google pass Object has to point at a Class. Call `setClass()` with the suffix you used when declaring it:

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;

EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    ->setAttendeeName('John Lennon')
    ->setSection('Floor A')
    ->setRow('12')
    ->setSeat('24')
    ->save();
```

Saving without a class throws a `RuntimeException`.

## Object IDs

Each Google pass Object also has its own unique ID. By default, the package generates a UUID for each Object you create. If you'd rather control the ID yourself (say, to line it up with a primary key from your database), pass a suffix:

```php
$builder->setObjectSuffix("ticket-{$ticketId}");
```
