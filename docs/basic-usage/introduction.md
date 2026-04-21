---
title: Introduction
weight: 1
---

Say you're selling event tickets. After checkout, you want the user to tap a button and drop the ticket straight into their iPhone wallet. With this package, that takes a builder call and a redirect.

Here's an Apple Wallet event ticket:

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;

$mobilePass = EventTicketPassBuilder::make()
    ->setOrganisationName('Eras Tour Promotions')
    ->setSerialNumber('TS-BRU-0042')
    ->setDescription('Taylor Swift at King Baudouin Stadium')
    ->addField('event', 'The Eras Tour')
    ->addField('attendee', 'Dan Johnson', label: 'Name')
    ->addField('seat', 'Floor A, Row 12')
    ->save();
```

A note on the fields. The first argument (`event`, `attendee`, `seat`) is a free-form identifier. Apple doesn't care what string you pick, but it has to be unique within the pass, and you'll reuse it later when you want to update that specific field. The label shown on the pass defaults to a title-cased version of the key. Pass `label:` when you want something different (like `'Name'` instead of `'Attendee'` above).

Apple passes have several field zones (header, primary, secondary, auxiliary, back) that control where a field shows up on the pass. `addField` defaults to primary, which is fine for getting started. When you need finer control, reach for `addHeaderField`, `addSecondaryField`, `addAuxiliaryField`, or `addBackField`. See [Generating your first pass](/docs/laravel-mobile-pass/v1/apple-wallet/generating-your-first-pass) for the full set.

The `save()` method returns a `MobilePass` model. Nothing is written to disk. All pass properties (fields, images, barcode) are stored as a row in the `mobile_passes` table.

To hand the ticket to the user, return the model straight from a controller. `MobilePass` implements `Responsable`, so Laravel serves the signed `.pkpass` for you:

```php
// in a controller
return $mobilePass;
```

The user taps through, sees a preview in Apple Wallet, and taps Add. At that moment, Apple calls back to your app to register the device against the pass. The package handles that endpoint and stores the registration in the `mobile_pass_registrations` table. That link between pass and device is what makes updates possible.

### Updating a pass

If the seat assignment changes later, update the field through the builder:

```php
$mobilePass->builder()
    ->updateField('seat', 'Floor A, Row 14')
    ->save();
```

If you want the user's device to display a notification when the value changes, pass a `changeMessage:`:

```php
$mobilePass->builder()
    ->updateField('seat', 'Floor A, Row 14', changeMessage: 'Your seat has changed to %@')
    ->save();
```

The `changeMessage` is stored on the field. Once set, Apple uses that template for every future value change on that field, not just this one update. The `%@` placeholder is substituted with the new value at notification time.

The package notifies Apple, Apple pings the device, and the device pulls the new version of the pass from your server. The ticket updates in place. No second download, no re-sent email.

## What about Google?

Android users live in Google Wallet, and the same package covers that with a matching set of builders. The flow is almost identical, with one extra step up front. Google requires you to declare a Class (a shared template for a batch of passes) before you can issue individual tickets. You do that once per event.

Once the class exists, building a ticket for one attendee looks like this:

```php
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

$mobilePass = EventTicketPassBuilder::make()
    ->setClass('taylor-swift-2026')
    ->setAttendeeName('Dan Johnson')
    ->setSection('Floor A')
    ->setRow('12')
    ->setSeat('24')
    ->setBarcode(Barcode::make(BarcodeType::QR, 'TICKET-12345'))
    ->save();
```

The `save()` method creates the Object on Google's servers and inserts a row in the same `mobile_passes` table. Handing it to the user is the same one-liner:

```php
// in a controller
return $mobilePass;
```

Android users get redirected to the Google Wallet save URL, iPhone users get the `.pkpass` download. The `Responsable` model picks the right response for the platform the pass was built for.

Updates also look the same from your side. You change values and call `save()`. The difference is on the wire. For Apple, your app pushes out the update. For Google, Google itself pushes it to the device, so you don't host a device-facing web service for Google passes.
