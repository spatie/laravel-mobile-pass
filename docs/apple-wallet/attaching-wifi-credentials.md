---
title: Attaching Wi-Fi credentials
weight: 5
---

Apple Wallet passes can carry Wi-Fi credentials. When the user's iPhone detects the pass is relevant (they're near the venue, the gate, the hotel), Wallet surfaces a "Join Wi-Fi network" button on the pass. One tap and the phone joins the network. No settings screen, no typing the password.

The feature is Apple-only. Google Wallet has no equivalent, so `setWifiDetails` is ignored for Google builders.

## Attaching a network

Pass one or more `WifiNetwork` entities through `setWifiDetails()`:

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\WifiNetwork;

EventTicketPassBuilder::make()
    ->setOrganisationName('Fab Four Promotions')
    ->setSerialNumber('BTL-SHEA-0042')
    ->setDescription('The Beatles at Shea Stadium')
    ->setWifiDetails(
        WifiNetwork::make('SheaStadium-Guest', 'welcome1965'),
    )
    ->save();
```

The entity is a simple SSID + password pair. Internally, the builder writes it to the pass's `semantics.wifiAccess` array, which is where Apple looks for these credentials.

## Multiple networks

You can attach more than one. Wallet presents them in order and the user picks one when they tap:

```php
$builder->setWifiDetails(
    WifiNetwork::make('SheaStadium-Guest', 'welcome1965'),
    WifiNetwork::make('SheaStadium-VIP', 'backstage1965'),
);
```

A typical use is a public guest network plus a faster sponsor network for specific ticket holders.

## When it actually surfaces

The "Join Wi-Fi" button doesn't show up just because the pass has the credentials. Apple only exposes it when the pass is contextually relevant. In practice that means you also want to set [pass relevance](/docs/laravel-mobile-pass/v1/apple-wallet/pass-relevance) so the pass surfaces on the lock screen at the right moment:

```php
use Illuminate\Support\Carbon;

$builder
    ->addLocation(
        latitude: 40.7559,
        longitude: -73.8456,
        relevantText: 'Welcome to Shea Stadium',
    )
    ->setRelevantDate(Carbon::parse('1965-08-15 19:00'))
    ->setWifiDetails(
        WifiNetwork::make('SheaStadium-Guest', 'welcome1965'),
    );
```

Now the pass comes forward an hour before the show when the user is near the stadium, with the Wi-Fi button visible.

Pass types that actually render the Wi-Fi button: boarding passes (iOS 12+), event tickets (iOS 13+). Other pass types accept the field but the user won't see a button.

## What networks it supports

- WPA2/WPA3 personal (pre-shared key).
- WPA-Enterprise (username + password) is not supported.
- Hidden SSIDs work but you'll want to confirm the exact SSID string matches what the AP broadcasts.

## Security considerations

The password lives in the pass in plain text. Anything that exports the pass (mail attachments, screenshots, iCloud backups) exposes it. Use this for networks whose password is meant to be shared, not corporate networks or guest networks that rotate daily.

Changing the password later means [updating the pass](/docs/laravel-mobile-pass/v1/basic-usage/updating-a-pass) so the new credentials land on the user's device. Devices that have already joined keep the old password cached until they forget the network.
