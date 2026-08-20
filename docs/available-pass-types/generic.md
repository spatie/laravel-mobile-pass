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

Generic passes also support Apple's poster layout, added in iOS 27, via a separate set of fields: `addPosterHeaderField()`, `addPosterPrimaryField()`, `addPosterFooterField()`, and `addPosterBackField()`. These populate a `posterGeneric` block. Adding any poster field is what makes that block appear, there's no separate toggle. Set an `artwork` image (see [Adding images](../basic-usage/adding-images)) too, since the poster layout renders it as a full-bleed background.

Keep filling the regular fields as well. Devices below iOS 27 don't understand `posterGeneric`, and a pass that only has poster fields won't install on them at all:

```php
GenericPassBuilder::make()
    // ...
    ->setArtworkImage(public_path('images/artwork.png'))
    ->addHeaderField('event', 'Spatie Conference 2026')
    ->addField('track', 'All-access')
    ->addPosterHeaderField('event', 'Spatie Conference 2026')
    ->addPosterPrimaryField('track', 'All-access')
    ->save();
```

Wallet only renders the first poster footer field and ignores the rest.

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
