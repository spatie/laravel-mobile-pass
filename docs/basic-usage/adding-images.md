---
title: Adding images
weight: 3
---

Apple and Google Wallet want pass artwork in very different shapes. Apple needs image files bundled into the signed `.pkpass`, so you point the builder at paths on disk. Google needs publicly reachable URLs, so you hand the Class a URL that Google's servers fetch.

## Apple

Every Apple pass takes a logo (top-left corner) and an icon (the square seen in notifications and email attachments). Boarding passes also take a footer image above the barcode.

Pass the path to the image file on disk:

```php
$builder
    ->setLogoImage(public_path('images/logo.png'))
    ->setIconImage(public_path('images/icon.png'));
```

Apple renders passes at 1x, 2x, and 3x pixel densities. Providing higher-density versions gives you crisper results; pass them as extra arguments:

```php
$builder->setLogoImage(
    x1Path: public_path('images/logo.png'),
    x2Path: public_path('images/logo@2x.png'),
    x3Path: public_path('images/logo@3x.png'),
);
```

If a path doesn't exist on disk, the builder throws an `InvalidArgumentException` right away so mistyped paths surface immediately.

### Recommended dimensions

Apple publishes the exact sizes it expects. The values below are for the 1x density; double them for 2x, triple for 3x.

| Image | 1x size (points) | Notes |
|---|---|---|
| Icon | 29 × 29 | Used in notifications and email attachments. Ship this one at minimum. |
| Logo | up to 160 × 50 | Top-left of the pass. |
| Thumbnail | up to 90 × 90 | Square artwork next to primary fields on event tickets and generic passes. |
| Strip | 375 × 123 (coupon) / 375 × 98 (event ticket) | Full-width image behind the primary fields. |
| Background | 180 × 220 | Event tickets only. Blurred and stretched by Wallet. |
| Footer | 286 × 15 | Boarding passes only. Sits above the barcode. |

Apple's docs don't strictly require any of these, but passes feel unfinished without an icon (it's what shows up on the lock screen and in Mail), so treat that one as mandatory in practice.

See Apple's [Pass Design and Creation](https://developer.apple.com/library/archive/documentation/UserExperience/Conceptual/PassKit_PG/Creating.html) chapter for the full table, exact pixel sizes for every density, and which images each pass style supports.

## Google

Google doesn't ship image bytes. You give the Class a URL, and Google fetches it when it renders the pass. The URL has to be publicly reachable over HTTPS. The methods live on the Class, not on the per-attendee Builder:

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;

EventTicketPassClass::make('beatles-shea-1965')
    // ...
    ->setLogoUrl('https://cdn.example.com/beatles-logo.png')
    ->setHeroImageUrl('https://cdn.example.com/beatles-hero.png')
    ->save();
```

Different Class types expose different image setters. Event tickets and boarding passes take a logo and a hero image. Loyalty programs take a program logo via `setProgramLogoUrl()`. Offers take a logo.

Google caches images it has fetched. When you change a URL's contents in place, you may need to use a new URL (or append a cache-busting query string) to see the new image.

See Google's [Visual design reference](https://developers.google.com/wallet/generic/resources/visual-design) for the sizes and formats it expects.

## Background colours

Both platforms support a background colour (handy when you're not using a background image). On Apple pass a hex string to `setBackgroundColour()`:

```php
$builder->setBackgroundColour('#1d72b8');
```

On Google hand a hex string to `setBackgroundColor()` on the Class:

```php
EventTicketPassClass::make('beatles-shea-1965')
    ->setBackgroundColor('#1d72b8')
    ->save();
```
