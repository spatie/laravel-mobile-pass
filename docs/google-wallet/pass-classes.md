---
title: Pass classes
weight: 1
---

Google Wallet splits a pass into two pieces: a Class and an Object. The Class is a shared template (the event itself, the loyalty program, the flight). The Object is one pass for one user, built on top of a Class.

Think of a Beatles concert. You declare the "Beatles at Shea Stadium on August 15" Class once. Every ticket sold is an Object pointing at that Class. Or think of a Starbucks loyalty program: the program is one Class, and each member's card is its own Object.

Apple has no equivalent of this. On Apple, every pass stands on its own.

## Declaring a Class

Every Google pass type has a matching Class. You create one by calling `make()` with a unique suffix, setting the template fields, then calling `save()`.

Here's the Beatles concert Class, declared once per event:

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;

EventTicketPassClass::make('beatles-shea-1965')
    ->setIssuerName('Fab Four Promotions')
    ->setEventName('The Beatles | Live at Shea')
    ->setVenueName('Shea Stadium')
    ->setVenueAddress('126th Street & Roosevelt Avenue, Flushing, NY')
    ->setStartDate(Carbon::parse('1965-08-15 20:00'))
    ->setLogoUrl('https://example.com/beatles-logo.png')
    ->setHeroImageUrl('https://example.com/beatles-hero.png')
    ->setBackgroundColor('#1a1a1a')
    ->save();
```

The suffix (`'beatles-shea-1965'` here) is what you'll reference later when creating individual ticket passes. The full class ID Google sees is `{issuer-id}.{suffix}`, which the package stitches together for you.

Google stores Classes on its own servers, not in your database.

## Fetching classes back

You can look a class up later by its suffix.

```php
$class = EventTicketPassClass::find('beatles-shea-1965');

if ($class) {
    // class exists on Google
}
```

Or list every class of this type on your issuer account:

```php
$classes = EventTicketPassClass::all();
```

Both methods return instances with the fields Google sends back hydrated onto them.

## Adding locations, links, and modules

Every Google class type supports a few extra building blocks that show up on the back of the pass: geographic locations, a links module (the "main page URL" and any other URIs), text modules, and image modules. Because these are shared across all class types, the same methods are available on every Class builder.

```php
EventTicketPassClass::make('beatles-shea-1965')
    ->setIssuerName('Fab Four Promotions')
    ->setEventName('The Beatles | Live at Shea')
    ->addLocation(40.7569, -73.8458)
    ->addLink('https://fabfour.example.com', 'Official site')
    ->addLink('tel:+15551234567')
    ->addTextModule('Doors', 'Doors open at 18:30')
    ->addImageModule('https://example.com/seating-chart.png', 'seating')
    ->save();
```

- `addLocation(float $latitude, float $longitude)` adds a point to the class's `locations`.
- `addLink(string $uri, ?string $description = null)` adds a URI to the links module. The description is optional.
- `addTextModule(string $header, string $body, ?string $id = null)` adds a text module.
- `addImageModule(string $imageUrl, ?string $id = null)` adds an image module. The image must be a hosted URL.

Each method can be called multiple times to add more than one entry. When you fetch a class back with `find()` or `all()`, these are hydrated onto the instance and readable through `getLocations()`, `getLinks()`, `getTextModules()`, and `getImageModules()`.

## Retiring a class

Google has no hard delete for classes. What you can do is call `retire()`, which flips the class's `reviewStatus` to `REJECTED`. Google will stop promoting it, but every pass you've already issued against it keeps working.

```php
EventTicketPassClass::find('beatles-shea-1965')?->retire();
```

## Available Class builders

The package ships a Class for each Google pass type:

- `EventTicketPassClass`
- `BoardingPassClass`
- `LoyaltyPassClass`
- `OfferPassClass`
- `GenericPassClass`

See [Object methods](google-wallet/object-methods) for how to issue a per-user pass once your Class is declared.
