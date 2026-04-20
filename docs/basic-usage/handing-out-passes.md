---
title: Handing out passes
weight: 7
---

Whether a pass is for Apple Wallet or Google Wallet, you hand it to the user the same way: call `addToWalletUrl()` on the `MobilePass` model. The model knows its platform and returns the correct link for that platform.

```php
$url = $pass->addToWalletUrl();
```

For Apple, this is a signed download URL that serves the `.pkpass` file. For Google, it's a `pay.google.com` save link that Google itself presents to the user.

## As a redirect

The simplest way to deliver a pass is to redirect the user to the URL:

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

class AddToWalletController
{
    public function __invoke(MobilePass $pass)
    {
        return redirect($pass->addToWalletUrl());
    }
}
```

## As a button or link

You can put the URL behind any anchor or form. This works nicely in a post-checkout confirmation page:

```blade
<a href="{{ $pass->addToWalletUrl() }}" class="btn">
    Add to Wallet
</a>
```

## In an email

The same URL goes in transactional emails. Send one pass in the welcome email, another when an event ticket is issued, and so on.

```blade
<p>Your ticket is ready!</p>

<a href="{{ $pass->addToWalletUrl() }}">
    Add it to your Wallet
</a>
```

## As a QR code

If you're printing confirmations or displaying them on another screen, wrap the URL in a QR code. Any QR library works.

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

QrCode::size(240)->generate($pass->addToWalletUrl());
```

## A note on the Apple URL

The Apple link is a `signedRoute`, not a `temporarySignedRoute`. By default it does not expire. This matches how Apple users expect wallet download links to work: they'll often open the email days later on a new device. If you want an expiring URL, override the download route or build your own wrapper around it.

## Apple-specific: direct download and mail attachments

When you need the raw `.pkpass` file rather than a URL (for an API response, or as a mail attachment), the `MobilePass` model still supports those older patterns.

```php
// Return as an HTTP response
return $pass;

// Explicit download with a custom filename
$pass->download('boarding-pass-london');

// Attach to an email
$mail->attach($pass);
```

These only work for Apple passes. Google passes are never served as files, they live on Google's servers.
