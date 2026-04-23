---
title: Customizing actions
weight: 2
---

The core behaviour of this package is split across a set of action classes, all registered in the config file. You can swap any of them out by extending the default class and pointing the config at your version.

Say you want to run some code right before the package notifies Apple that a pass has been updated. Create a class that extends `NotifyAppleOfPassUpdateAction` and override the `execute` method:

```php
namespace App\Actions;

use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Models\MobilePass;

class CustomNotifyAppleOfPassUpdateAction extends NotifyAppleOfPassUpdateAction
{
    public function execute(MobilePass $mobilePass)
    {
        // Your custom code here

        parent::execute($mobilePass);
    }
}
```

Then register your class in the `mobile-pass` config file:

```php
// config/mobile-pass.php

return [
    // other keys

    'actions' => [
        // other actions
    
        'notify_apple_of_pass_update' => \App\Actions\CustomNotifyAppleOfPassUpdateAction::class,
    ],
];
```

## Available actions

The five actions below are registered under the `actions` key in `config/mobile-pass.php`. Each one can be swapped out independently.

### register_device

Default: `Spatie\LaravelMobilePass\Actions\Apple\RegisterDeviceAction`

Runs when Apple's device calls the register-device endpoint after the user taps Add to Wallet. It creates an `AppleMobilePassRegistration` row tying the device to the pass, then fires the `MobilePassAdded` event. Override this to hook into registrations (for audit trails, push-token tracking against another store, anti-abuse checks).

### unregister_device

Default: `Spatie\LaravelMobilePass\Actions\Apple\UnregisterDeviceAction`

Runs when Apple's device calls the unregister-device endpoint after the user removes the pass. It deletes the matching `AppleMobilePassRegistration` rows and fires `MobilePassRemoved` for each. Override to add cleanup logic or to soft-delete instead of hard-delete.

### notify_apple_of_pass_update

Default: `Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction`

Runs after a `MobilePass` row is updated for an Apple pass. It signs and sends an APNs push to every device registered against the pass, so each device re-fetches the updated `.pkpass`. Override to add retry logic, rate limiting, or to swap in your own APNs transport.

### notify_google_of_pass_update

Default: `Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction`

Runs after a `MobilePass` row is updated for a Google pass. It sends a PATCH request to Google's Wallet REST API with the new object payload, and Google fans the change out to devices. Override to add extra telemetry, to patch additional fields, or to short-circuit when you know Google already has the latest state.

### handle_google_callback

Default: `Spatie\LaravelMobilePass\Actions\Google\HandleGoogleCallbackAction`

Runs when Google calls back to your app with a save or remove event. It resolves the `MobilePass`, stores a `GoogleMobilePassEvent` row with the raw JWT claims, then fires `MobilePassAdded` or `MobilePassRemoved`. Override to change how callbacks are recorded (say, if you want to keep them in a separate audit table) or to enrich the resolved pass before the event fires.

## Which action to override

As a rule of thumb:

- For side effects (logging, analytics, notifications), listen to the `MobilePassAdded` / `MobilePassRemoved` events instead. See [Events](/docs/laravel-mobile-pass/v1/advanced-usage/events). Events are cheaper to set up and won't break if the action's internals change.
- Override an action when the event hook isn't enough, for example when you want to change the persistence shape (different columns, different models), swap transport (custom APNs client), or reject the operation outright.
