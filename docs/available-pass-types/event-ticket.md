---
title: Event ticket
weight: 3
---

Event tickets cover concerts, festivals, sports events, conferences, and anything else where someone shows up at a specific time and place. Both platforms have a dedicated `EventTicketPassBuilder`.

## Apple

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;

EventTicketPassBuilder::make()
    ->setOrganisationName('Fab Four Promotions')
    ->setSerialNumber('BTL-SHEA-0042')
    ->setDescription('The Beatles at Shea Stadium')
    ->addPrimaryField('event', 'Beatles Live at Shea')
    ->addSecondaryField('section', 'B12')
    ->addSecondaryField('seat', 'Row 8, Seat 22')
    ->save();
```

## Google

Declare the Class once per event (the venue, the show, the shared visuals), then create an Object per ticket.

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

// Once, per event
EventTicketPassClass::make('beatles-shea-1965')
    ->setIssuerName('Fab Four Promotions')
    ->setEventName('Beatles Live at Shea')
    ->setVenueName('Shea Stadium')
    ->setVenueAddress('126th Street & Roosevelt Avenue, Flushing, NY')
    ->setStartDate(now()->addMonths(2))
    ->setLogoUrl('https://cdn.example.com/beatles-logo.png')
    ->save();

// Per ticket
EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    ->setAttendeeName('Dan Johnson')
    ->setSection('B12')
    ->setRow('8')
    ->setSeat('22')
    ->setBarcode(Barcode::make(BarcodeType::QR, 'BTL-SHEA-0042'))
    ->save();
```
