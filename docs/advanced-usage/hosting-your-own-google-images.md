---
title: Hosting your own Google images
weight: 5
---

Google Wallet expects every image on a pass (logo, hero image, program artwork) to live at a public HTTPS URL. Google fetches the image itself. It doesn't accept uploaded bytes the way Apple's `.pkpass` format does.

The package gives you two ways to provide an image.

## Pass a public URL (recommended)

If your images already live on a CDN, S3 bucket, or any public host, hand Google the URL directly:

```php
use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;

EventTicketPassClass::make('beatles-shea-1965')
    ->setLogoUrl('https://cdn.example.com/beatles-logo.png')
    ->setHeroImageUrl('https://cdn.example.com/beatles-hero.png')
    ->save();
```

Every Class builder exposes `setLogoUrl()`, `setHeroImageUrl()`, and friends. Under the hood they call `Image::fromUrl()`. This is the simplest path and what most apps should use.

## Use a local file

If you want to point at a file on your server, reach for `Image::fromLocalPath()`:

```php
use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;

$image = Image::fromLocalPath(public_path('images/ticket-hero.png'));
```

This is only supported on object-level builders in v1. Calling `publicUrl()` on a local-path image throws for now, because the package hasn't yet shipped a hosted image route. Feeding `Image::fromLocalPath()` into a Class (which runs the Class validators) will raise a `RuntimeException`.

> A hosted image route is on the v1.1 roadmap. Until it ships, class-level images must be URLs.

For now, upload your pass images to a public host before referencing them. A small S3 bucket or a Cloudflare R2 bucket with a public domain is the easiest path.
