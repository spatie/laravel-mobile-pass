---
title: Declaring Google pass classes
weight: 3
---

Google Wallet separates a pass into two pieces: a **Class** and an **Object**. A Class is a shared template (the event itself, the loyalty program, the flight). An Object is one pass for one user, built on top of a Class.

Think of a Taylor Swift concert. The "Taylor Swift at Wembley on June 1" Class is declared once. Every ticket sold is an Object pointing at that Class. Or think of a Starbucks loyalty program: the program is one Class, and each member's card is a separate Object.

Apple has no equivalent of this. On Apple, every pass is standalone.

## Declare a Class

Every Google pass type has a matching Class. Create one by calling `make()` with a unique suffix, set the template fields, then `save()`.

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;

EventTicketPassClass::make('taylor-swift-2026')
    ->setIssuerName('Eras Tour Promotions')
    ->setEventName('Taylor Swift | The Eras Tour')
    ->setVenueName('Wembley Stadium')
    ->setVenueAddress('London HA9 0WS, United Kingdom')
    ->setStartDate(now()->addMonths(2))
    ->setLogoUrl('https://example.com/taylor-logo.png')
    ->setHeroImageUrl('https://example.com/taylor-hero.png')
    ->setBackgroundColor('#1a1a1a')
    ->save();
```

The suffix (`'taylor-swift-2026'` here) is what you'll reference later when creating individual ticket passes. The full class ID Google sees is `{issuer-id}.{suffix}`, which the package builds for you.

## Where to declare classes

Google Classes are stored on Google's servers, not in your database. You declare them once. Three common patterns:

- **Seeder**, when the class is static (like a long-running loyalty program). This is the default recommendation.
- **Dedicated artisan command**, when you want to re-run class creation from CI or from your server.
- **Admin action**, when non-developers need to spin up new classes (like an event organiser creating a new concert in your app).

Here's a seeder example:

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassClass;

class LoyaltyClassSeeder extends Seeder
{
    public function run(): void
    {
        LoyaltyPassClass::make('spatie-rewards')
            ->setIssuerName('Spatie')
            ->setProgramName('Spatie Rewards')
            ->setProgramLogoUrl('https://spatie.be/logo.png')
            ->setBackgroundColor('#1d72b8')
            ->save();
    }
}
```

## Fetch classes back

You can look a class up later by its suffix.

```php
$class = EventTicketPassClass::find('taylor-swift-2026');

if ($class) {
    // class exists on Google
}
```

Or list every class of this type on your issuer account:

```php
$classes = EventTicketPassClass::all();
```

Both methods return instances with the fields Google sends back hydrated onto them.

## Retire a class

Google has no hard delete for classes. What you can do is call `retire()`, which flips the class's `reviewStatus` to `REJECTED`. Google will stop promoting it, but every pass already issued against it keeps working.

```php
EventTicketPassClass::find('taylor-swift-2026')?->retire();
```

## Available Class builders

The package ships a Class for each Google pass type:

- `EventTicketPassClass`
- `BoardingPassClass`
- `LoyaltyPassClass`
- `OfferPassClass`
- `GenericPassClass`
