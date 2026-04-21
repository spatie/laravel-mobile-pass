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
    ->setPassengerName('Paul McCartney')
    ->setSeats(Seat::make(number: '12A'))
    ->addField('departure', 'AUH', label: 'Abu Dhabi')
    ->addField('destination', 'LHR', label: 'London')
    ->save();
```

For non-airline transit (trains, boats, buses), `BoardingPassBuilder` is abstract. Subclass it yourself and set `$transitType` to whichever `TransitType` case fits:

```php
use Spatie\LaravelMobilePass\Builders\Apple\BoardingPassBuilder;
use Spatie\LaravelMobilePass\Enums\TransitType;

class TrainPassBuilder extends BoardingPassBuilder
{
    protected ?TransitType $transitType = TransitType::Train;
}
```

Once that class exists, build a pass with it exactly the same way as `AirlinePassBuilder`:

```php
TrainPassBuilder::make()
    ->setOrganisationName('SNCB')
    ->setSerialNumber('TICKET-456')
    ->setDescription('Brussels to Antwerp, coach 3')
    ->setPassengerName(PersonName::make(givenName: 'George', familyName: 'Harrison'))
    ->setSeats(Seat::make(number: '24B'))
    ->addField('departure', 'BRU', label: 'Brussels-Central')
    ->addField('destination', 'ANT', label: 'Antwerp-Central')
    ->save();
```

`TransitType` has `Air`, `Train`, `Boat`, and `Generic` cases.

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
    ->setLocalScheduledDepartureDateTime(Carbon::parse('2026-04-20 14:30'))
    ->setLogoUrl('https://cdn.example.com/lh-logo.png')
    ->save();

// Per passenger
BoardingPassBuilder::make()
    ->setClass('lh123-2026-04-20')
    ->setPassengerName('Paul McCartney')
    ->setSeatNumber('12A')
    ->setConfirmationCode('ABC123')
    ->save();
```
