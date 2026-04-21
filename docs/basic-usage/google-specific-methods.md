---
title: Google-specific methods
weight: 7
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

Once the Class is on Google, you issue one Object per attendee, pointing at that Class:

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

## Object-level methods

Every Google builder extends `GooglePassBuilder`, which handles the bits of a pass that are Object-specific. Look-and-feel (logos, colours, event name, venue) lives on the Class, covered above.

### Referencing the Class

Every Google pass Object has to point at a Class. Call `setClass()` with the suffix you used when declaring it:

```php
EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    // ...
    ->save();
```

Saving without a class throws a `RuntimeException`.

### Object IDs

Each Google pass Object also has its own unique ID. By default, the package generates a UUID for each Object you create. If you'd rather control the ID yourself (say, to line it up with a primary key from your database), pass a suffix:

```php
$builder->setObjectSuffix("ticket-{$ticketId}");
```

### Barcodes

Pass a `Barcode` entity to render a barcode on the Google pass:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

$builder->setBarcode(Barcode::make(BarcodeType::QR, 'TICKET-12345'));
```

The `Barcode` entity lives in the `Apple\Entities` namespace because the same entity drives both platforms. The Google builder translates it into Google's barcode payload shape (`QR_CODE`, `PDF_417`, `AZTEC`, `CODE_128`) for you.
