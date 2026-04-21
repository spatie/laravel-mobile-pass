---
title: Updating passes
weight: 3
---

When your user downloads a pass and adds it to their Wallet, Apple calls back to your app to confirm the pass has been registered.

The package handles that incoming request for you and stores the registration in the `mobile_pass_registrations` table, linked to the `MobilePass` model that generated the pass. That link is what lets you push updates later.

Here's how you update a pass that's already in a user's Wallet:

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

$mobilePass = $user->firstMobilePass();

$mobilePass->updateField('seat', '13A');
```

When you update a pass, the package notifies Apple for you. Apple pings the device to say a new version is available, and the device fetches it from your server.

If you want the user's device to show a notification when the value changes, pass a `changeMessage:`:

```php
$mobilePass->updateField(
    'seat',
    '13A',
    changeMessage: 'Your seat was changed to :value',
);
```

If you need to update a handful of fields at once (and save only once), drop down to the builder:

```php
$mobilePass->builder()
    ->updateField('seat', '13A')
    ->updateField('gate', 'D68')
    ->save();
```

The `:value` placeholder in the message is swapped out for the new field value. The `changeMessage` is stored on the field, so once you set it, Apple fires it for every future value change on that field until you overwrite it.
