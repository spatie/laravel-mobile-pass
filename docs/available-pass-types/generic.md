---
title: Generic
weight: 6
---

When none of the other pass types fit, generic is your escape hatch. It's useful for conference badges, access passes, identification cards, and anything else that doesn't look like a ticket, coupon, or loyalty card. Both platforms have a `GenericPassBuilder`.

## Apple

```php
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;

GenericPassBuilder::make()
    ->setOrganizationName('Spatie Conference')
    ->setSerialNumber('BADGE-042')
    ->setDescription('Conference attendee badge')
    ->addField('name', 'Ringo Starr')
    ->addSecondaryField('track', 'All-access')
    ->save();
```

## Google

Declare the Class once per use case (the brand, the visuals, the card title), then create an Object per person.

```php
use Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassClass;

// Once, per use case
GenericPassClass::make('spatie-conference-badge')
    ->setIssuerName('Spatie Conference')
    ->setCardTitle('Spatie Conference 2026')
    ->setSubheader('Attendee badge')
    ->setLogoUrl('https://cdn.example.com/conf-logo.png')
    ->save();

// Per attendee
GenericPassBuilder::make()
    ->setClass('spatie-conference-badge')
    ->setHeader('Ringo Starr')
    ->setCardTitle('All-access badge')
    ->setSubheader('Speaker')
    ->save();
```
