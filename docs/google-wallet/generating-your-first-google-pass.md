---
title: Generating your first Google pass
weight: 3
---

Once you have a [Google pass class](declaring-google-pass-classes) declared, generating an individual pass for a user takes one builder call. The builder creates the Object on Google's servers and returns a `MobilePass` model you can store or hand to a controller.

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

The `Barcode` entity is imported from the `Apple\Entities` namespace because it is shared across both platforms. The Google builders translate it into Google's barcode payload shape (`QR_CODE`, `PDF_417`, `AZTEC`, `CODE_128`) for you.

`save()` does three things:

1. Validates the payload.
2. Creates the Object on Google.
3. Inserts a row in the `mobile_passes` table and returns the `MobilePass` model.

## Handing the pass to the user

`MobilePass` is `Responsable`, so to let a user add the pass to their Google Wallet you can return the model directly. Laravel will redirect them to the right Google Wallet save URL:

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

Google handles the rest: the user sees the pass preview, taps Save, and it lands in their wallet.

For more ways to distribute the URL (buttons, emails, QR codes), see [Handing out passes](handing-out-passes).

## Other Google builders

Every Google pass type has a matching builder:

- `EventTicketPassBuilder`
- `BoardingPassBuilder`
- `LoyaltyPassBuilder`
- `OfferPassBuilder`
- `GenericPassBuilder`

Each one exposes setters specific to its pass type. Boarding passes have `setPassengerName()` and `setSeatNumber()`. Loyalty passes have `setAccountId()` and `setBalanceMicros()`. Offers have `setTitle()` and `setRedemptionCode()`. They all share `setClass()`, `setBarcode()`, and `save()`.
