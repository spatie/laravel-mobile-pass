---
title: Updating passes
weight: 3
---

When your user downloads a pass and adds it to their Wallet, Apple sends a request to confirm that the pass has been registered.

The package handles that incoming request and stores the registration in the `mobile_pass_registrations` table. The registration is associated with the `MobilePass` model that was used to generate the pass.

Here's how you update a saved pass:

```php
use App\Models\MobilePass;

$mobilePass = $user->firstMobilePass();

$mobilePass->builder()
    ->updateField('seat', '13A')
    ->save();
```

When a pass gets updated, the package notifies Apple. Apple tells the device that a new version is available, and the device fetches it from your server.

If you want the user's device to show a notification when the value changes, pass a `changeMessage:`:

```php
$mobilePass->builder()
    ->updateField('seat', '13A', changeMessage: 'Your seat was changed to %@')
    ->save();
```

The `%@` placeholder is substituted with the new value by Apple when the notification is rendered.
