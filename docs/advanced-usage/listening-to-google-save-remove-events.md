---
title: Listening to Google save and remove events
weight: 3
---

When a user saves a Google pass to their wallet (or removes one), Google calls back to your app. The package verifies the callback, records it to the database, and fires a Laravel event you can listen for.

## Configure the callback endpoint

The package mounts the callback endpoint inside the `Route::mobilePass()` macro. If you've called `Route::mobilePass()` with no argument, the full path is `/passkit/v1/google/callbacks`. If you passed a prefix, that prefix sits in front.

```php
// in your routes file
Route::mobilePass();
// endpoint is at: https://your-app.com/passkit/v1/google/callbacks

Route::mobilePass('api');
// endpoint is at: https://your-app.com/api/passkit/v1/google/callbacks
```

Whichever URL you ended up with, head to the [Google Pay & Wallet Business Console](https://pay.google.com/business/console), find the Callback URL field, and paste it in.

Google signs the callback with an RS256 JWT, so you need to set the signing key:

```bash
MOBILE_PASS_GOOGLE_CALLBACK_SIGNING_KEY="-----BEGIN PUBLIC KEY-----
MIIB...
-----END PUBLIC KEY-----"
```

You'll find that key in the Business Console under Settings, API access. See [Getting credentials from Google](/docs/laravel-mobile-pass/v1/installation-setup/getting-credentials-from-google) for the full walkthrough.

## Listen for the events

Two Laravel events fire on each callback:

- `Spatie\LaravelMobilePass\Events\GoogleMobilePassSaved`
- `Spatie\LaravelMobilePass\Events\GoogleMobilePassRemoved`

Both receive the `MobilePass` model and the stored `GoogleMobilePassEvent`.

```php
namespace App\Listeners;

use Spatie\LaravelMobilePass\Events\GoogleMobilePassSaved;

class TrackPassSaves
{
    public function handle(GoogleMobilePassSaved $event): void
    {
        $event->mobilePass->user->update([
            'wallet_pass_saved_at' => $event->receivedAt,
        ]);
    }
}
```

Register the listener in your app's `EventServiceProvider` as usual.

## Query the history

Every callback is stored as a `GoogleMobilePassEvent` related to the `MobilePass`. You can walk the history at any time:

```php
$mobilePass->googleEvents;

$mobilePass->googleEvents()->saves()->get();
$mobilePass->googleEvents()->removes()->get();
```

To know whether the pass is currently on the user's phone, use the helper:

```php
if ($mobilePass->isCurrentlySavedToGoogleWallet()) {
    // Latest callback was a save
}
```

The helper checks the most recent event. If the user saved, removed, then saved again, it returns `true`.
