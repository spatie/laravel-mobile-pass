---
title: Adding images
weight: 3
---

Apple and Google Wallet want pass artwork in very different shapes. Apple needs image files bundled into the signed `.pkpass`, so you point the builder at paths on disk. Google needs publicly reachable URLs, so you hand the Class a URL that Google's servers fetch.

## Apple

Every Apple pass takes a logo (top-left corner) and an icon (the square seen in notifications and email attachments). Boarding passes also take a footer image above the barcode. Other pass types optionally accept a strip, thumbnail, or background image depending on the layout.

Pass a `Spatie\LaravelMobilePass\Builders\Apple\Entities\Image` entity with the path(s) to your file(s):

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;

$builder
    ->setLogoImage(Image::make(x1Path: public_path('images/logo.png')))
    ->setIconImage(Image::make(x1Path: public_path('images/icon.png')));
```

Apple renders passes at 1x, 2x, and 3x pixel densities. You can ship just a 1x and let Wallet scale it, but you'll get crisper results by providing the higher-density versions too:

```php
Image::make(
    x1Path: public_path('images/logo.png'),
    x2Path: public_path('images/logo@2x.png'),
    x3Path: public_path('images/logo@3x.png'),
);
```

`Image::make()` throws an `InvalidArgumentException` if any of the paths you pass don't exist, so mistyped paths surface immediately.

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

## Background colours

Both platforms support a background colour (handy when you're not using a background image). On Apple pass an RGB `Colour` to `setBackgroundColour()`:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Colour;

$builder->setBackgroundColour(Colour::makeFromHex('#1d72b8'));
```

On Google hand a hex string to `setBackgroundColor()` on the Class:

```php
EventTicketPassClass::make('beatles-shea-1965')
    ->setBackgroundColor('#1d72b8')
    ->save();
```
