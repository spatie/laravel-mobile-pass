---
title: Updating passes
weight: 4
---

When your user downloads a pass and adds it to their Wallet, Apple will send a request to confirm that the pass has been registered.

The package will handle the incoming request, and store the registration in the `mobile_pass_registrations` table. This registration will be associated the `MobilePass` model that was used to generate the pass that was downloaded by the user.

Here's how you can update a saved pass.

```php
use App\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\FieldContent;

$mobilePass = $user->firstMobilePass();

$mobilePass
    ->builder()
    ->updateField('seat', fn (FieldContent $field) =>
        $field->setValue('13A')
    )
    ->save();
```

When a pass gets updated, the package will notify Apple that the pass has been updated.

Apple will then tell the device that a new version of the pass is available.

Then, the device requests the latest version of the pass from your server.

Here's how you can trigger a push notification for a change:

```php
use App\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\FieldContent;

$mobilePass = $user->firstMobilePass();

$mobilePass
    ->builder()
    ->updateField('seat', fn (FieldContent $field) =>
        $field
            ->setValue('13A')
            ->showMessageWhenChanged("Your seat was changed")
    )
    ->save();
```
