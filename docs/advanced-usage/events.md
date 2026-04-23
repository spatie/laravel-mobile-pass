---
title: Events
weight: 5
---

The package dispatches Laravel events at key moments in the pass lifecycle so your app can react without having to extend any action classes.

## MobilePassAdded

The `Spatie\LaravelMobilePass\Events\MobilePassAdded` event fires when a user adds the pass to their wallet. It covers both platforms:

- Apple, when an iPhone calls the register-device endpoint after the user taps Add in Wallet. The event fires once per new `(device, pass)` registration. Re-registrations of the same device don't re-fire.
- Google, when Google sends a `save` callback for the pass.

The event carries the `MobilePass` model. If you need to branch on platform, call `$event->mobilePass->isApple()` or `$event->mobilePass->isGoogle()`, or read the full enum at `$event->mobilePass->platform`.

```php
namespace App\Listeners;

use Spatie\LaravelMobilePass\Events\MobilePassAdded;

class TrackPassInstalls
{
    public function handle(MobilePassAdded $event): void
    {
        $event->mobilePass->user->update([
            'wallet_pass_installed_at' => now(),
        ]);
    }
}
```

For Apple passes this is your chance to record that a specific device has the pass. The full registration row (device id, push token, pass type) is reachable through `$event->mobilePass->registrations` if you need it.

## MobilePassRemoved

The `Spatie\LaravelMobilePass\Events\MobilePassRemoved` event fires when the user removes the pass from their wallet. Like the added event, it covers both platforms:

- Apple, when the device calls the unregister-device endpoint. The event fires once per deleted registration, so a pass removed from three devices fires three times.
- Google, when Google sends a `del` callback.

Same payload shape:

```php
namespace App\Listeners;

use Spatie\LaravelMobilePass\Events\MobilePassRemoved;

class ReactToUninstall
{
    public function handle(MobilePassRemoved $event): void
    {
        // e.g. send a follow-up email, flip a flag, log an analytics event
    }
}
```

On Apple, a user can keep the pass on other devices after removing it from one, so don't treat a single `MobilePassRemoved` as "the pass is gone from this user's life." If you need that, check `$event->mobilePass->registrations()->exists()` in the listener.

## AppleMobilePassLogsReceived

The `Spatie\LaravelMobilePass\Events\AppleMobilePassLogsReceived` event fires when Apple posts error log entries to the package's log endpoint (`/passkit/v1/log`). Apple devices use this to report problems they hit while handling a pass.

```php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Spatie\LaravelMobilePass\Events\AppleMobilePassLogsReceived;

class ForwardAppleWalletLogs
{
    public function handle(AppleMobilePassLogsReceived $event): void
    {
        foreach ($event->logEntries as $line) {
            Log::channel('apple-wallet')->info($line);
        }
    }
}
```

The payload is a plain `array<string>` of log lines. There's no Google equivalent; Google Wallet doesn't post back error logs.

## Registering listeners

Laravel 11+ auto-discovers listeners in `app/Listeners` by convention. If you've opted into explicit registration, add entries to your `EventServiceProvider`:

```php
protected $listen = [
    \Spatie\LaravelMobilePass\Events\MobilePassAdded::class => [
        \App\Listeners\TrackPassInstalls::class,
    ],
    \Spatie\LaravelMobilePass\Events\MobilePassRemoved::class => [
        \App\Listeners\ReactToUninstall::class,
    ],
];
```

## Checking whether a pass is currently in the wallet

If you don't care about the event stream and just want to know whether the pass is installed right now, use the unified helper:

```php
if ($mobilePass->isCurrentlyInWallet()) {
    // Apple: at least one device has it registered
    // Google: latest save/remove callback was a save
}
```

The helper dispatches by platform so you don't need to care which one the pass belongs to.

## Querying the underlying history

Both platforms also expose their raw history if you want it.

For Google, every callback is persisted as a `GoogleMobilePassEvent` row tied to the `MobilePass`:

```php
$mobilePass->googleEvents;

$mobilePass->googleEvents()->saves()->get();
$mobilePass->googleEvents()->removes()->get();
```

Or use the Google-specific helper if you want just the "is it saved" check:

```php
$mobilePass->isCurrentlySavedToGoogleWallet();
```

For Apple, `$mobilePass->registrations` gives you the set of currently registered devices. Every row is an `AppleMobilePassRegistration`; Google passes always have an empty collection here. An empty collection on an Apple pass means no iPhone currently has the pass installed (or the device never called the unregister endpoint, which does happen; Apple isn't strict about it).
