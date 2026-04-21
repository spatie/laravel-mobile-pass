---
title: Delivering passes to users
weight: 2
---

Once a `MobilePass` exists, you've got a handful of ways to get it in front of the user. The sections below cover the common delivery surfaces: controllers, buttons, and QR codes on printed confirmations. Whatever surface you pick, the mechanics are the same for Apple and Google, since the package figures out the right link for you.

## Generating a URL

Call `addToWalletUrl()` on the `MobilePass` model to get a shareable link. The model knows its platform and returns the right one.

```php
$url = $mobilePass->addToWalletUrl();
```

For Apple, that's a signed download URL serving the `.pkpass` file. For Google, it's a `pay.google.com` save link that Google itself presents to the user.

The URL works behind any anchor or form. This sits nicely on a post-checkout confirmation page:

```blade
<a href="{{ $mobilePass->addToWalletUrl() }}" class="btn">
    Add to Wallet
</a>
```

## Returning the model from a controller

The `MobilePass` model implements `Responsable`, so the simplest way to deliver a pass is to return the model itself from a controller. Laravel takes it from there, serving the signed `.pkpass` for Apple passes and redirecting to the Google Wallet save URL for Google passes. One controller, both platforms.

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

If you need the URL itself (for a button, a QR code, or somewhere else entirely), call `$mobilePass->addToWalletUrl()` and embed the string wherever you like.

## As a QR code

If you're printing confirmations or showing them on another screen, wrap the URL in a QR code. Any QR library works.

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

QrCode::size(240)->generate($mobilePass->addToWalletUrl());
```

## A note on the Apple URL

The Apple link is a `signedRoute`, not a `temporarySignedRoute`, so by default it doesn't expire. That matches how wallet download links usually get used: people open them days later on a new device. If you want an expiring URL, override the download route or wrap your own thing around it.

## As an email attachment

For Apple passes, you can attach the `.pkpass` file directly to a mailable. The `MobilePass` model implements Laravel's `Attachable` contract, so attaching it is a one-liner. Google passes aren't files (Google hosts them for you), so this route is Apple-only.

Here's the full Mailable:

```php
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Spatie\LaravelMobilePass\Models\MobilePass;

class TicketPurchased extends Mailable
{
    public function __construct(
        public User $user,
        public MobilePass $mobilePass,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your ticket');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.ticket-purchased');
    }

    /** @return array<int, MobilePass> */
    public function attachments(): array
    {
        return [$this->mobilePass];
    }
}
```

The attachment lands in the inbox as a `.pkpass` file named after the pass's download name. iPhones recognise the MIME type (`application/vnd.apple.pkpass`) and offer to add it to Wallet straight from Mail.
