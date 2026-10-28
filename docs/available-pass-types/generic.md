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

Generic passes also support Apple's poster layout via a separate set of fields: `addPosterHeaderField()`, `addPosterPrimaryField()`, `addPosterFooterField()`, and `addPosterBackField()`. These populate a `posterGeneric` block. There's no separate toggle: adding any poster field is what makes that block appear. Wallet renders only the first poster footer field and ignores the rest.

Set an `artwork` image (see [Adding images](../basic-usage/adding-images)) too, since the poster layout renders it as a full-bleed background.

Keep filling the regular fields as well. Older devices don't understand `posterGeneric`, and this package won't stop you from shipping a pass that only carries poster fields:

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

## Google

Google's generic Class is a thin template. Unlike the other Class types it carries no title, header, images or colors: those all live on the Object. Declare the Class once per use case (with any shared links, text or image modules), then create an Object per person carrying the visuals.

```php
use Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassClass;

// Once, per use case
GenericPassClass::make('spatie-conference-badge')
    ->addLink('https://conference.spatie.be', 'Conference website')
    ->save();

// Per attendee
GenericPassBuilder::make()
    ->setClass('spatie-conference-badge')
    ->setCardTitle('Spatie Conference 2026')
    ->setHeader('Ringo Starr')
    ->setSubheader('Speaker')
    ->setLogoUrl('https://cdn.example.com/conf-logo.png')
    ->setHeroImageUrl('https://cdn.example.com/conf-hero.png')
    ->setBackgroundColor('#1d72b8')
    ->save();
```

Google requires a card title and a header on every generic Object. `setCardTitle()`, `setHeader()` and `setSubheader()` accept either a string or a `LocalizedString` with translations.

`GenericPassClass::setIssuerName()`, `setCardTitle()`, `setHeader()`, `setSubheader()`, `setLogoUrl()`, `setHeroImageUrl()`, `setBackgroundColor()` and `addLocation()` are deprecated. Google's generic Class has none of those fields, so their values are no longer sent (Google rejected them). Set them on `GenericPassBuilder` instead.
