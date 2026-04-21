---
title: Generating your first pass
weight: 1
---

Say you're selling event tickets. After checkout, you want the user to tap a button and drop the ticket straight into their iPhone wallet. With this package, that takes a builder call and a redirect.

## Before you start

Grab credentials for the platform(s) you want to support. Each walkthrough lists the environment variables you need to set:

- [Getting credentials from Apple](/docs/laravel-mobile-pass/v1/apple-wallet/getting-credentials-from-apple)
- [Getting credentials from Google](/docs/laravel-mobile-pass/v1/google-wallet/getting-credentials-from-google)

If you want to link passes to a user (or any other model) so you can look them up later, add the `HasMobilePasses` trait to that model:

```php
use Spatie\LaravelMobilePass\Models\Concerns\HasMobilePasses;

class User extends Model
{
    use HasMobilePasses;
}
```

Once you've associated passes with a model, see [Retrieving mobile passes](/docs/laravel-mobile-pass/v1/basic-usage/retrieving-mobile-passes) for the helper methods that fetch them back.

## Building a pass

Here's an Apple Wallet event ticket:

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;

$mobilePass = EventTicketPassBuilder::make()
    ->setOrganisationName('Fab Four Promotions')
    ->setSerialNumber('BTL-SHEA-0042')
    ->setDescription('The Beatles at Shea Stadium')
    ->addField('event', 'Beatles Live at Shea')
    ->addField('attendee', 'Dan Johnson', label: 'Name')
    ->addField('seat', 'Floor A, Row 12')
    ->save();
```

Those first arguments (`event`, `attendee`, `seat`) are identifiers you pick. Apple doesn't care what they say, they just have to be unique within the pass so you can refer back to them later when you want to update that specific field. The label that shows up on the pass is the identifier, title-cased. Pass `label:` when you want something different, which is why `'attendee'` reads as `'Name'` on this ticket.

There are a few other zones a field can land in (header, primary, secondary, auxiliary, back), and they change where the field appears on the pass. `addField` drops it in the primary zone, which is the right place most of the time. When you want finer control, reach for `addHeaderField`, `addSecondaryField`, `addAuxiliaryField`, or `addBackField`. The [Apple walkthrough](/docs/laravel-mobile-pass/v1/apple-wallet/generating-your-first-pass) shows them all in one place.

Calling `save()` gives you back a `MobilePass` model. Nothing is written to disk; the whole pass (fields, images, barcode) lives as a row in the `mobile_passes` table.

Handing the ticket to the user is as simple as returning the model from a controller. `MobilePass` implements `Responsable`, so Laravel takes care of serving the signed `.pkpass` file:

```php
// in a controller
return $mobilePass;
```

The user taps through, sees the pass preview in Apple Wallet, and taps Add. Apple then calls back to your app to register the device against the pass. The package handles that endpoint for you and saves the registration in the `mobile_pass_registrations` table. That link between pass and device is what lets you push updates later on.

### Updating a pass

Say the seat assignment changes after the user already has the ticket in Wallet. Call `updateField` directly on the model:

```php
$mobilePass->updateField('seat', 'Floor A, Row 14');
```

If you want the user's device to display a notification when the value changes, pass a `changeMessage:`:

```php
$mobilePass->updateField(
    'seat',
    'Floor A, Row 14',
    changeMessage: 'Your seat has changed to :value',
);
```

The `:value` placeholder is replaced with the new field value when the notification shows. The `changeMessage` is stored on the field, so once set, Apple fires it for every future value change on that field until you overwrite it.

The package notifies Apple, Apple pings the device, and the device pulls the new version of the pass from your server. The ticket updates in place, no second download.

## What about Google?

Android users live in Google Wallet, and the same package covers that with a matching set of builders. The flow is almost identical, with one extra step up front. Google requires you to declare a Class (a shared template for a batch of passes) before you can issue individual tickets. You do that once per event.

Once the class exists, building a ticket for one attendee looks like this:

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

The `save()` method creates the Object on Google's servers and inserts a row in the same `mobile_passes` table. Handing it to the user is the same one-liner:

```php
// in a controller
return $mobilePass;
```

Android users get redirected to the Google Wallet save URL, iPhone users get the `.pkpass` download. The `Responsable` model picks the right response for the platform the pass was built for.

Updates also look the same from your side. You change values and call `save()`. The difference is on the wire. For Apple, your app pushes out the update. For Google, Google itself pushes it to the device, so you don't host a device-facing web service for Google passes.
