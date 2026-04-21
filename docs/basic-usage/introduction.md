---
title: Introduction
weight: 1
---

Say you're selling event tickets. After checkout, you want the user to tap a button and drop the ticket straight into their iPhone wallet. With this package, that takes a builder call and a redirect.

Here's an Apple Wallet event ticket:

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\FieldContent;

$pass = EventTicketPassBuilder::make()
    ->setOrganisationName('Eras Tour Promotions')
    ->setSerialNumber('TS-BRU-0042')
    ->setDescription('Taylor Swift at King Baudouin Stadium')
    ->setPrimaryFields(
        FieldContent::make('event')
            ->withLabel('Event')
            ->withValue('The Eras Tour'),
    )
    ->setSecondaryFields(
        FieldContent::make('attendee')
            ->withLabel('Name')
            ->withValue('Dan Johnson'),
        FieldContent::make('seat')
            ->withLabel('Seat')
            ->withValue('Floor A, Row 12'),
    )
    ->save();
```

`save()` returns a `MobilePass` model. Nothing is written to disk. All pass properties (fields, images, barcode) are stored as a row in the `mobile_passes` table.

To hand the ticket to the user, redirect them to the URL the model gives you:

```php
return redirect($pass->addToWalletUrl());
```

The user taps through, sees a preview in Apple Wallet, and taps Add. At that moment, Apple calls back to your app to register the device against the pass. The package handles that endpoint and stores the registration in the `mobile_pass_registrations` table. That link between pass and device is what makes updates possible.

If the seat assignment changes later, update the field through the builder:

```php
$pass
    ->builder()
    ->updateField('seat', fn (FieldContent $field) =>
        $field->setValue('Floor A, Row 14')
    )
    ->save();
```

The package notifies Apple, Apple pings the device, and the device pulls the new version of the pass from your server. The ticket updates in place. No second download, no re-sent email.

## What about Google?

Android users live in Google Wallet, and the same package covers that with a matching set of builders. The flow is almost identical, with one extra step up front. Google requires you to declare a **Class** (a shared template for a batch of passes) before you can issue individual tickets. You do that once per event.

Once the class exists, building a ticket for one attendee looks like this:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

$pass = EventTicketPassBuilder::make()
    ->setClass('taylor-swift-2026')
    ->setAttendeeName('Dan Johnson')
    ->setSection('Floor A')
    ->setRow('12')
    ->setSeat('24')
    ->setBarcode(Barcode::make(BarcodeType::QR, 'TICKET-12345'))
    ->save();
```

`save()` creates the Object on Google's servers and inserts a row in the same `mobile_passes` table. Handing it to the user is the same call:

```php
return redirect($pass->addToWalletUrl());
```

Android picks up the Google Wallet save link, iPhone users get the `.pkpass` download. `$pass->addToWalletUrl()` returns whatever is right for the platform the pass was built for.

Updates also look the same from your side. You change values and call `save()`. The difference is on the wire. For Apple, your app pushes out the update. For Google, Google itself pushes it to the device, so you don't host a device-facing web service for Google passes.
