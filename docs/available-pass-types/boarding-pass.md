---
title: Boarding pass
weight: 2
---

Boarding passes cover flights, trains, buses, and boats. Apple ships two first-party builders — `AirlinePassBuilder` for flights and `TrainPassBuilder` for trains — and the more general `BoardingPassBuilder` is what you subclass yourself, picking a `TransitType`, for buses or boats. Google has a single `BoardingPassBuilder` that works for flights.

## Apple

```php
use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\PersonName;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;

AirlinePassBuilder::make()
    ->setOrganizationName('Etihad')
    ->setSerialNumber('TICKET-123')
    ->setDescription('Etihad flight EY066 boarding pass')
    ->setDepartureAirportCode('AUH')
    ->setDestinationAirportCode('LHR')
    ->setPassengerName(PersonName::make(givenName: 'Paul', familyName: 'McCartney'))
    ->setSeats(Seat::make(number: '12A'))
    ->addField('departure', 'AUH', label: 'Abu Dhabi')
    ->addField('destination', 'LHR', label: 'London')
    ->save();
```

Besides the `number` shown above, a `Seat` can carry a `description`, `identifier`, `row`, `section`, `type`, `aisle`, `level` and `sectionColor`. All of them are optional, and `setSeats()` accepts more than one `Seat`.

For trains, `TrainPassBuilder` ships with the package and works exactly like `AirlinePassBuilder`:

```php
use Spatie\LaravelMobilePass\Builders\Apple\TrainPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\PersonName;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;

TrainPassBuilder::make()
    ->setOrganizationName('SNCB')
    ->setSerialNumber('TICKET-456')
    ->setDescription('Brussels to Antwerp, coach 3')
    ->setPassengerName(PersonName::make(givenName: 'George', familyName: 'Harrison'))
    ->setCarNumber('3')
    ->setDeparturePlatform('A')
    ->setDepartureStationName('Brussels-Central')
    ->setDestinationPlatform('2')
    ->setDestinationStationName('Antwerp-Central')
    ->setSeats(Seat::make(number: '24B'))
    ->addField('departure', 'BRU', label: 'Brussels-Central')
    ->addField('destination', 'ANT', label: 'Antwerp-Central')
    ->save();
```

For other non-airline transit (buses, boats), `BoardingPassBuilder` is abstract. Subclass it yourself and set `$transitType` to whichever `TransitType` case fits:

```php
use Spatie\LaravelMobilePass\Builders\Apple\BoardingPassBuilder;
use Spatie\LaravelMobilePass\Enums\TransitType;

class BusPassBuilder extends BoardingPassBuilder
{
    protected ?TransitType $transitType = TransitType::Bus;
}
```

`TransitType` has `Air`, `Train`, `Bus`, `Boat`, and `Generic` cases.

Any boarding pass — airline, train, or a DIY bus/boat subclass — can also carry these general tags:

- `setBoardingZone()`, `setTicketFareClass()`, `setMembershipProgramStatus()`
- `setDepartureCityName()`, `setDestinationCityName()`
- `setDepartureLocationTimeZone()`, `setDestinationLocationTimeZone()`
- `setInternationalDocumentsAreVerified()`, `setInternationalDocumentsVerifiedDeclarationName()`
- `setDepartureLocationSecurityPrograms()`, `setDestinationLocationSecurityPrograms()`, `setPassengerEligibleSecurityPrograms()` (all take `TransitSecurityProgram` cases)
- `setLoungePlaceIds()`

`AirlinePassBuilder` adds four more, airline-only:

- `setPassengerAirlineSsrs()`, `setPassengerInformationSsrs()`, `setPassengerServiceSsrs()`
- `setPassengerCapabilities()` (takes `PassengerCapability` cases)

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
