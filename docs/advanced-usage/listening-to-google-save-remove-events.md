---
title: Listening to Google save and remove events
weight: 3
---

When a user saves a Google pass to their wallet (or removes one), Google calls back to your app. The package verifies the callback, records it to the database, and fires a Laravel event you can listen for.

## Configure the callback endpoint

The package mounts the callback endpoint at `{prefix}/passkit/v1/google/callbacks`, wherever you called `Route::mobilePass()`. Go to your Google Pay & Wallet Business Console and set the **Callback URL** to this full URL (for example `https://your-app.com/passkit/v1/google/callbacks`).

Google signs the callback with an RS256 JWT. Set the signing key:

```bash
MOBILE_PASS_GOOGLE_CALLBACK_SIGNING_KEY="-----BEGIN PUBLIC KEY-----
MIIB...
-----END PUBLIC KEY-----"
```

You can find this key in the Business Console under Settings, API access. See [Getting credentials from Google](/docs/laravel-mobile-pass/v1/basic-usage/getting-credentials-from-google) for the full walkthrough.

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
$pass->googleEvents;

$pass->googleEvents()->saves()->get();
$pass->googleEvents()->removes()->get();
```

To know whether the pass is currently on the user's phone, use the helper:

```php
if ($pass->isCurrentlySavedToGoogleWallet()) {
    // Latest callback was a save
}
```

This checks the most recent event. If the user saved, removed, then saved again, the helper returns `true`.
