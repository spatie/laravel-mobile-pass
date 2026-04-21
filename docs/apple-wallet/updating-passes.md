---
title: Updating passes
weight: 3
---

When your user downloads a pass and adds it to their Wallet, Apple sends a request to confirm that the pass has been registered.

The package handles that incoming request and stores the registration in the `mobile_pass_registrations` table. The registration is associated with the `MobilePass` model that was used to generate the pass.

Here's how you update a saved pass:

```php
use Spatie\LaravelMobilePass\Models\MobilePass;

$mobilePass = $user->firstMobilePass();

$mobilePass->updateField('seat', '13A');
```

When a pass gets updated, the package notifies Apple. Apple tells the device that a new version is available, and the device fetches it from your server.

If you want the user's device to show a notification when the value changes, pass a `changeMessage:`:

```php
$mobilePass->updateField('seat', '13A', changeMessage: 'Your seat was changed to :value');
```

If you need to update several fields in one go (and save only once), drop down to the builder:

```php
$mobilePass->builder()
    ->updateField('seat', '13A')
    ->updateField('gate', 'D68')
    ->save();
```

The `:value` placeholder is replaced with the new field value in the notification. The `changeMessage` is stored on the field, so once set, Apple fires it for every future value change on that field until you overwrite it.
