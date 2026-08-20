---
title: Event ticket
weight: 3
---

Event tickets cover concerts, festivals, sports events, conferences, and anything else where someone shows up at a specific time and place. Both platforms have a dedicated `EventTicketPassBuilder`.

## Apple

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;

EventTicketPassBuilder::make()
    ->setOrganizationName('Fab Four Promotions')
    ->setSerialNumber('BTL-SHEA-0042')
    ->setDescription('The Beatles at Shea Stadium')
    ->addField('event', 'Beatles Live at Shea')
    ->addSecondaryField('section', 'B12')
    ->addSecondaryField('seat', 'Row 8, Seat 22')
    ->save();
```

Call `usePosterLayout()` to opt into Apple's poster-style layout, which renders the `artwork` image (see [Adding images](../basic-usage/adding-images)) as a full-bleed background. Devices that don't support it fall back to the classic layout automatically:

```php
EventTicketPassBuilder::make()
    // ...
    ->setArtworkImage(public_path('images/artwork.png'))
    ->usePosterLayout()
    ->save();
```

Apple gates this layout behind an entitlement: your pass needs to be NFC-enabled, and the layout has to be enabled for your team. Until Apple has approved you, `usePosterLayout()` has no visible effect and Wallet keeps showing the classic layout. Check [Apple's Wallet documentation](https://developer.apple.com/documentation/walletpasses) for the current requirements.

Event tickets can also carry venue details, which Wallet shows in the pass's event guide panel. Where the venue is:

- `setVenueName()`, `setVenueRegionName()`, `setVenueRoom()`
- `setVenueLocation()`, `setVenuePhoneNumber()`
- `setVenueEntrance()`, `setVenueEntranceDoor()`, `setVenueEntranceGate()`, `setVenueEntrancePortal()`

And when things happen there:

- `setVenueOpenDate()` and `setVenueCloseDate()`
- `setVenueDoorsOpenDate()`, `setVenueGatesOpenDate()`
- `setVenueFanZoneOpenDate()`, `setVenueBoxOfficeOpenDate()`, `setVenueParkingLotsOpenDate()`

There's also a `venueMap` image via `setVenueMapImage()`:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;

EventTicketPassBuilder::make()
    // ...
    ->setVenueName('Amsterdam ArenA')
    ->setVenueLocation(Location::make(52.3143, 4.9416))
    ->setVenueMapImage(public_path('images/venue-map.png'))
    ->save();
```

The event guide only shows up when the pass is relevant for a date, so call `setRelevantDate()` as well (see [Pass relevance](../apple-wallet/pass-relevance)). Note that `setVenueLocation()` only carries the coordinates: the altitude and relevant text a `Location` can hold are used by `addLocation()`, not by this semantic tag.

Event tickets can also carry event details Wallet shows alongside the venue guide. Tags valid for any event:

- `setEventName()`, `setEventType()`, `setEventStartDate()`, `setEventStartDateInfo()`, `setEventEndDate()`
- `setAdmissionLevel()`, `setAdmissionLevelAbbreviation()`, `setAttendeeName()`
- `setAdditionalTicketAttributes()`, `setEntranceDescription()`, `setGenre()`
- `setTailgatingAllowed()`, `setDuration()`, `setSilenceRequested()`
- `setSeats()`

For live performances:

- `setPerformerNames()`, `setArtistIds()`, `setAlbumIds()`, `setPlaylistIds()`

For sports events:

- `setAwayTeamAbbreviation()`, `setAwayTeamName()`, `setAwayTeamLocation()`
- `setHomeTeamAbbreviation()`, `setHomeTeamName()`, `setHomeTeamLocation()`
- `setLeagueAbbreviation()`, `setLeagueName()`, `setSportName()`

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\EventDateInfo;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;
use Spatie\LaravelMobilePass\Enums\EventType;

EventTicketPassBuilder::make()
    // ...
    ->setEventType(EventType::LivePerformance)
    ->setEventStartDateInfo(EventDateInfo::make(
        date: Carbon::parse('2026-08-19 18:00'),
        timeZone: 'America/New_York',
    ))
    ->setSeats(Seat::make(number: '22', row: '8', section: 'B12'))
    ->save();
```

## Google

Declare the Class once per event (the venue, the show, the shared visuals), then create an Object per ticket.

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

// Once, per event
EventTicketPassClass::make('beatles-shea-1965')
    ->setIssuerName('Fab Four Promotions')
    ->setEventName('Beatles Live at Shea')
    ->setVenueName('Shea Stadium')
    ->setVenueAddress('126th Street & Roosevelt Avenue, Flushing, NY')
    ->setStartDate(Carbon::parse('1965-08-15 20:00'))
    ->setLogoUrl('https://cdn.example.com/beatles-logo.png')
    ->save();

// Per ticket
EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    ->setAttendeeName('John Lennon')
    ->setSection('B12')
    ->setRow('8')
    ->setSeat('22')
    ->setBarcode(BarcodeType::Qr, 'BTL-SHEA-0042')
    ->save();
```
