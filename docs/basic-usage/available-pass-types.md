---
title: Available pass types
weight: 9
---

The package ships a builder for each pass type Apple Wallet and Google Wallet support. Pick the builder that matches the kind of pass you're issuing. Every builder has setters specific to its type, on top of the shared `save()`, `addToWalletUrl()`, and `expire()` you already know.

| Apple                       | Google                      | Typical use                            |
| --------------------------- | --------------------------- | -------------------------------------- |
| `AirlinePassBuilder`        | `BoardingPassBuilder`       | Flight boarding passes                 |
| `EventTicketPassBuilder`    | `EventTicketPassBuilder`    | Concerts, festivals, sports events     |
| `CouponPassBuilder`         | `OfferPassBuilder`          | Discount codes, limited-time offers    |
| `StoreCardPassBuilder`      | `LoyaltyPassBuilder`        | Loyalty cards, membership programs     |
| `GenericPassBuilder`        | `GenericPassBuilder`        | Anything that doesn't fit the above    |

The Apple and Google pairs aren't interchangeable. Each platform has its own namespace (`Spatie\LaravelMobilePass\Builders\Apple\...` and `Spatie\LaravelMobilePass\Builders\Google\...`). If you want to support both platforms for the same conceptual pass, you build twice. The `MobilePass` model is shared and the fluent API is consistent, so there's very little boilerplate either way.

The sections below show a minimal example per pass type. For the full Apple walkthrough see [Generating your first pass](/docs/laravel-mobile-pass/v1/apple-wallet/generating-your-first-pass). For Google, see [Generating your first Google pass](/docs/laravel-mobile-pass/v1/google-wallet/generating-your-first-google-pass).

## Apple Wallet

### Airline boarding pass

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

### Event ticket

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

### Coupon

```php
use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;

CouponPassBuilder::make()
    ->setOrganisationName('Spatie Store')
    ->setSerialNumber('COUPON-SUMMER25')
    ->setDescription('20% off everything this summer')
    ->addPrimaryField('offer', '20%', label: 'Save')
    ->addSecondaryField('expires', '2026-08-31')
    ->save();
```

### Store card (loyalty)

```php
use Spatie\LaravelMobilePass\Builders\Apple\StoreCardPassBuilder;

StoreCardPassBuilder::make()
    ->setOrganisationName('Spatie Rewards')
    ->setSerialNumber('CARD-USER-7842')
    ->setDescription('Spatie Rewards member card')
    ->addPrimaryField('balance', '1,250', label: 'Points')
    ->addSecondaryField('member', 'Dan Johnson')
    ->addSecondaryField('tier', 'Gold')
    ->save();
```

### Generic

```php
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;

GenericPassBuilder::make()
    ->setOrganisationName('Spatie Conference')
    ->setSerialNumber('BADGE-042')
    ->setDescription('Conference attendee badge')
    ->addPrimaryField('name', 'Dan Johnson')
    ->addSecondaryField('track', 'All-access')
    ->save();
```

## Google Wallet

Every Google pass type needs a Class declared first. The examples below show the full flow (declare the Class, then create the Object). Once the Class exists, every subsequent Object only needs its own `save()`. For more on the Class concept, see [Declaring Google pass classes](/docs/laravel-mobile-pass/v1/google-wallet/declaring-google-pass-classes).

### Boarding pass (flight)

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

### Event ticket

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

### Offer (coupon)

```php
use Spatie\LaravelMobilePass\Builders\Google\OfferPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\OfferPassClass;

// Once, per offer
OfferPassClass::make('summer-2026-20off')
    ->setIssuerName('Spatie Store')
    ->setTitle('Save 20% this summer')
    ->setProvider('Spatie')
    ->setRedemptionChannel('ONLINE')
    ->setFinePrint('One use per customer. Expires 2026-08-31.')
    ->setLogoUrl('https://cdn.example.com/spatie-logo.png')
    ->save();

// Per redeemer
OfferPassBuilder::make()
    ->setClass('summer-2026-20off')
    ->setTitle('20% off for Dan')
    ->setRedemptionCode('DAN20SUMMER')
    ->save();
```

### Loyalty

```php
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassClass;

// Once, per program
LoyaltyPassClass::make('spatie-rewards')
    ->setIssuerName('Spatie')
    ->setProgramName('Spatie Rewards')
    ->setProgramLogoUrl('https://cdn.example.com/spatie-logo.png')
    ->setAccountNameLabel('Member')
    ->setAccountIdLabel('Member ID')
    ->setBackgroundColor('#1d72b8')
    ->save();

// Per member
LoyaltyPassBuilder::make()
    ->setClass('spatie-rewards')
    ->setAccountId('USER-7842')
    ->setAccountName('Dan Johnson')
    ->setBalanceString('1,250 points')
    ->save();
```

### Generic

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
    ->setHeader('Dan Johnson')
    ->setCardTitle('All-access badge')
    ->setSubheader('Speaker')
    ->save();
```
