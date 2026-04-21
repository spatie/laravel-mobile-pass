---
title: Pass relevance
weight: 5
---

Apple Wallet can surface a pass on the lock screen at the right moment. A boarding pass appears before the flight, a coffee loyalty card surfaces when the user walks into the shop, a concert ticket shows up as the doors open. This is called pass relevance, and it's driven by a date, one or more locations, or both.

Relevance is Apple-only. Google Wallet handles this for you based on the Class data you already provide.

For the full Apple spec, see [Location and Time Relevance](https://developer.apple.com/library/archive/documentation/UserExperience/Conceptual/PassKit_PG/Creating.html#//apple_ref/doc/uid/TP40012195-CH4-SW5) in the PassKit Programming Guide and the reference for [`Pass.Relevance`](https://developer.apple.com/documentation/walletpasses/pass/relevance).

## A relevant date

Pass `setRelevantDate()` a `Carbon` instance. Apple starts surfacing the pass about four hours before that moment.

```php
use Illuminate\Support\Carbon;

$builder->setRelevantDate(Carbon::parse('2026-08-15 20:00', 'America/New_York'));
```

When the user's device crosses into that window, the pass slides onto the lock screen. Once the event has passed, the pass drops back out of relevance.

## Locations

Attach one or more physical locations and Wallet will bring the pass forward when the user is near one of them. Latitude and longitude are all you have to provide:

```php
$builder
    ->addLocation(latitude: 40.7559, longitude: -73.8456)
    ->addLocation(latitude: 40.7580, longitude: -73.9855);
```

Optional arguments let you add altitude (useful when two points share coordinates but sit at different floors) and a message that shows up on the lock screen when the pass surfaces.

```php
$builder->addLocation(
    latitude: 40.7559,
    longitude: -73.8456,
    relevantText: 'Welcome to Shea Stadium',
);
```

Apple allows up to ten locations per pass.

## Tuning the radius

By default Apple surfaces the pass within a few hundred metres of a location. Override that with `setMaxDistance()`, which takes a distance in metres:

```php
$builder->setMaxDistance(500);
```

Pick a radius that matches the venue: larger for an airport, tighter for a coffee shop.

## Combining them

Relevance is strongest when a date and a location agree. A concert ticket with both the showtime and the stadium coordinates will surface before the show on the way to the venue, and drop out again once the event is over.

See Apple's [Pass Design and Creation](https://developer.apple.com/library/archive/documentation/UserExperience/Conceptual/PassKit_PG/Creating.html) for the full relevance model.
