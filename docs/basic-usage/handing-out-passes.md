---
title: Delivering passes to users
weight: 7
---

Once a `MobilePass` exists, you've got a handful of ways to get it in front of the user. The sections below cover the common delivery surfaces: controllers, buttons, emails, and QR codes on printed confirmations. Whatever surface you pick, the mechanics are the same for Apple and Google, since the package figures out the right link for you.

## Generating a URL

Call `addToWalletUrl()` on the `MobilePass` model to get a shareable link. The model knows its platform and returns the right one.

```php
$url = $mobilePass->addToWalletUrl();
```

For Apple, that's a signed download URL serving the `.pkpass` file. For Google, it's a `pay.google.com` save link that Google itself presents to the user.

## Returning the model from a controller

The `MobilePass` model implements `Responsable`, so the simplest way to deliver a pass is to return the model itself. Laravel serves the signed `.pkpass` for Apple passes and redirects to the Google Wallet save URL for Google passes, so you don't need to branch on platform:

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

class AddToWalletController
{
    public function __invoke(MobilePass $mobilePass)
    {
        return $mobilePass;
    }
}
```

If you need the URL itself (for an email link, a button, a QR code), call `$mobilePass->addToWalletUrl()` and embed the string wherever you like.

## As a button or link

The URL works behind any anchor or form. This sits nicely on a post-checkout confirmation page:

```blade
<a href="{{ $mobilePass->addToWalletUrl() }}" class="btn">
    Add to Wallet
</a>
```

## In an email

The same URL goes in transactional emails. Send one pass in the welcome email, another when an event ticket is issued, and so on.

```blade
<p>Your ticket is ready!</p>

<a href="{{ $mobilePass->addToWalletUrl() }}">
    Add it to your Wallet
</a>
```

## As a QR code

If you're printing confirmations or showing them on another screen, wrap the URL in a QR code. Any QR library works.

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

QrCode::size(240)->generate($mobilePass->addToWalletUrl());
```

## A note on the Apple URL

The Apple link is a `signedRoute`, not a `temporarySignedRoute`, so by default it doesn't expire. That matches how wallet download links usually get used: people open them days later on a new device. If you want an expiring URL, override the download route or wrap your own thing around it.

## Apple-specific: explicit download and mail attachments

For Apple passes you can also reach for the raw `.pkpass` file directly. That's useful when you want a custom filename or you're attaching the pass to a mailable.

```php
// Explicit download with a custom filename
$mobilePass->download('boarding-pass-london');

// Attach to an email
$mail->attach($mobilePass);
```

These only work for Apple passes. Google passes are never served as files. They live on Google's servers.
