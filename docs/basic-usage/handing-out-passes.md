---
title: Handing out passes
weight: 7
---

Whether a pass is for Apple Wallet or Google Wallet, you hand it to the user the same way: call `addToWalletUrl()` on the `MobilePass` model. The model knows its platform and returns the correct link for that platform.

```php
$url = $mobilePass->addToWalletUrl();
```

For Apple, this is a signed download URL that serves the `.pkpass` file. For Google, it's a `pay.google.com` save link that Google itself presents to the user.

## Returning the model from a controller

`MobilePass` implements `Responsable`, so the simplest way to deliver a pass is to return the model itself. Laravel serves the signed `.pkpass` for Apple passes and redirects to the Google Wallet save URL for Google passes — you don't have to branch:

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

You can put the URL behind any anchor or form. This works nicely in a post-checkout confirmation page:

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

If you're printing confirmations or displaying them on another screen, wrap the URL in a QR code. Any QR library works.

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

QrCode::size(240)->generate($mobilePass->addToWalletUrl());
```

## A note on the Apple URL

The Apple link is a `signedRoute`, not a `temporarySignedRoute`. By default it does not expire. This matches how Apple users expect wallet download links to work: they'll often open the email days later on a new device. If you want an expiring URL, override the download route or build your own wrapper around it.

## Apple-specific: explicit download and mail attachments

For Apple passes you can also reach for the raw `.pkpass` file directly — useful when you want a custom filename or you're attaching the pass to a mailable.

```php
// Explicit download with a custom filename
$mobilePass->download('boarding-pass-london');

// Attach to an email
$mail->attach($mobilePass);
```

These only work for Apple passes. Google passes are never served as files; they live on Google's servers.
