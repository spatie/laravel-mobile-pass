---
title: Generating your first Google pass
weight: 3
---

Once you have a [Google pass class](declaring-google-pass-classes) declared, generating a pass for a single user takes one builder call. The builder creates the Object on Google's servers and hands you back a `MobilePass` model you can store or return from a controller.

Here's an event ticket built on top of the `'beatles-shea-1965'` class:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

$mobilePass = EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    ->setAttendeeName('Dan Johnson')
    ->setSection('Floor A')
    ->setRow('12')
    ->setSeat('24')
    ->setBarcode(Barcode::make(BarcodeType::QR, 'TICKET-12345'))
    ->save();
```

The `Barcode` entity comes from the `Apple\Entities` namespace because it's shared across both platforms. The Google builders translate it into Google's barcode payload shape (`QR_CODE`, `PDF_417`, `AZTEC`, `CODE_128`) for you.

Calling `save()` does three things:

1. Validates the payload.
2. Creates the Object on Google.
3. Inserts a row in the `mobile_passes` table and returns the `MobilePass` model.

## Handing the pass to the user

The `MobilePass` model is `Responsable`, so you can return it straight from a controller and Laravel will redirect the user to the right Google Wallet save URL:

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

Google takes it from there: the user sees the pass preview, taps Save, and it lands in their wallet.

For other ways to distribute the URL (buttons, emails, QR codes), see [Handing out passes](handing-out-passes).

## Other Google builders

Every Google pass type has a matching builder:

- `EventTicketPassBuilder`
- `BoardingPassBuilder`
- `LoyaltyPassBuilder`
- `OfferPassBuilder`
- `GenericPassBuilder`

Each one exposes setters specific to its pass type. Boarding passes have `setPassengerName()` and `setSeatNumber()`. Loyalty passes have `setAccountId()` and `setBalanceMicros()`. Offers have `setTitle()` and `setRedemptionCode()`. They all share `setClass()`, `setBarcode()`, and `save()`.
