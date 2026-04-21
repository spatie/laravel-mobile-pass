---
title: Boarding pass
weight: 2
---

Boarding passes cover flights, trains, buses, and boats. Apple's `AirlinePassBuilder` is the flight-specific one; the more general `BoardingPassBuilder` lets you pick a `TransitType` if you're issuing trains or buses. Google has a single `BoardingPassBuilder` that works for flights.

## Apple

```php
use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;

AirlinePassBuilder::make()
    ->setOrganisationName('Etihad')
    ->setSerialNumber('TICKET-123')
    ->setDescription('Etihad flight EY066 boarding pass')
    ->setDepartureAirportCode('AUH')
    ->setDestinationAirportCode('LHR')
    ->setPassengerName('Dan Johnson')
    ->setSeats(Seat::make(number: '12A'))
    ->addPrimaryField('departure', 'AUH', label: 'Abu Dhabi')
    ->addPrimaryField('destination', 'LHR', label: 'London')
    ->save();
```

For non-airline transit (trains, boats, buses), subclass `BoardingPassBuilder` and pick your own `TransitType`.

## Google

Google boarding passes are flight-specific. Declare the Class once per flight, then create an Object per passenger.

```php
use Spatie\LaravelMobilePass\Builders\Google\BoardingPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\BoardingPassClass;

// Once, per flight
BoardingPassClass::make('lh123-2026-04-20')
    ->setIssuerName('Lufthansa')
    ->setAirlineCode('LH')
    ->setFlightNumber('LH123')
    ->setOriginAirportCode('FRA')
    ->setDestinationAirportCode('JFK')
    ->setLocalScheduledDepartureDateTime(now()->addWeek()->setTime(14, 30))
    ->setLogoUrl('https://cdn.example.com/lh-logo.png')
    ->save();

// Per passenger
BoardingPassBuilder::make()
    ->setClass('lh123-2026-04-20')
    ->setPassengerName('Dan Johnson')
    ->setSeatNumber('12A')
    ->setConfirmationCode('ABC123')
    ->save();
```
