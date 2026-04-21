---
title: Updating a pass
weight: 2
---

A pass on someone's phone isn't frozen. Seat assignments shift, gates change, loyalty balances tick up, coupons get extended. Push those changes through the `MobilePass` model and the package handles the platform-specific mechanics for you.

## Apple

For Apple passes, use `updateField` on the model. It replaces the value for the given key and saves in one call:

```php
$mobilePass->updateField('seat', '13A');
```

The package notifies Apple through APNs, Apple pings the user's device, and the device fetches the new version from your server. The user sees the updated pass in Wallet without any second download or re-sent email.

If you want the user's device to display a notification when the value changes, pass a `changeMessage:`:

```php
$mobilePass->updateField(
    'seat',
    '13A',
    changeMessage: 'Your seat was changed to :value',
);
```

The `:value` placeholder is replaced with the new field value when the notification renders. The `changeMessage` is stored on the field, so once you set it, Apple fires it for every future value change on that field until you overwrite it.

If you need to update several fields at once and only save once, drop down to the builder:

```php
$mobilePass->builder()
    ->updateField('seat', '13A')
    ->updateField('gate', 'D68')
    ->save();
```

## Google

Google passes don't use `updateField`. Instead, update the relevant keys on the `MobilePass` model's `content` array and save the model. The package notices the update and pushes the change to Google's Wallet API. Google takes care of notifying the device:

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

$mobilePass = MobilePass::find($id);

$content = $mobilePass->content;
$content['googleObjectPayload']['ticketHolderName'] = 'John Winston Lennon';

$mobilePass->update(['content' => $content]);
```

Under the hood, the package dispatches `NotifyGoogleOfPassUpdateAction`, which patches the Object on Google via the Wallet REST API. Once Google has the new state, it fans the update out to every device the pass lives on.

## Running update pushes asynchronously

By default, both Apple and Google update pushes run synchronously. If the upstream API is slow, so is your request. For high-traffic apps, move the push onto a queue by setting `MOBILE_PASS_QUEUE_CONNECTION`. See [Queueing update pushes](/docs/laravel-mobile-pass/v1/advanced-usage/queueing-update-pushes) for the full setup.

## Customising the update action

If you want to run your own code around the update push (logging, audit trails, retry policies, anything in that neighbourhood), extend either `NotifyAppleOfPassUpdateAction` or `NotifyGoogleOfPassUpdateAction` and register your class in the config. See [Customizing actions](/docs/laravel-mobile-pass/v1/advanced-usage/customizing-actions) for the walkthrough.
