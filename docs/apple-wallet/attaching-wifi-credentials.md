---
title: Attaching Wi-Fi credentials
weight: 5
---

There are two ways to ship Wi-Fi credentials on an Apple Wallet pass, and they behave very differently. Pick the one that matches how users are supposed to join.

- Encode the credentials in the pass's QR code. Anyone (including the pass holder) scans the barcode with their phone's camera and the OS offers to join. Works cross-platform, no relevance or entitlement needed.
- Attach them via the `wifiAccess` semantic tag. A "Join Wi-Fi network" button shows up inside the pass holder's own Wallet app when the pass is contextually relevant. Apple-only, narrower pass-type support.

## Option 1: a Wi-Fi QR code

Use the standard `WIFI:` URI format on the pass's barcode. iOS's camera app and Android's camera both understand this format and prompt the user to join when scanned.

```php
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

GenericPassBuilder::make()
    ->setOrganisationName('Spatie')
    ->setDescription('Guest Wi-Fi')
    ->addField('ssid', 'Spatie Guest', label: 'Network')
    ->addSecondaryField('password', 'welcome', label: 'Password')
    ->setBarcode(
        BarcodeType::Qr,
        'WIFI:S:Spatie Guest;T:WPA;P:welcome;;',
        altText: 'Spatie Guest',
    )
    ->save();
```

The `WIFI:` URI format is:

```
WIFI:S:<ssid>;T:<WPA|WPA2|WPA3|nopass>;P:<password>;H:<true|false>;;
```

`S` is the SSID, `T` is the security type, `P` is the password, `H` marks the network as hidden. Escape semicolons, colons, commas, and backslashes in the SSID or password with a backslash.

This approach shines when other people scan your pass to join. Pin it in Wallet for your home network, hand the QR to a guest, let coworkers scan the pass displayed on your phone.

## Option 2: the wifiAccess semantic tag

Apple's `wifiAccess` semantic tag drives a dedicated "Join Wi-Fi network" button inside Wallet. Call `addWifiNetwork()` with the SSID and password:

```php
use Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder;

EventTicketPassBuilder::make()
    ->setOrganisationName('Fab Four Promotions')
    ->setSerialNumber('BTL-SHEA-0042')
    ->setDescription('The Beatles at Shea Stadium')
    ->addWifiNetwork('SheaStadium-Guest', 'welcome1965')
    ->save();
```

Chain multiple calls to attach more than one network:

```php
$builder
    ->addWifiNetwork('SheaStadium-Guest', 'welcome1965')
    ->addWifiNetwork('SheaStadium-VIP', 'backstage1965');
```

### When the button actually surfaces

The button doesn't show up just because the pass has the credentials. Apple only exposes it when the pass is contextually relevant. In practice that means you also want to set [pass relevance](/docs/laravel-mobile-pass/v1/apple-wallet/pass-relevance) so the pass surfaces on the lock screen at the right moment:

```php
use Illuminate\Support\Carbon;

$builder
    ->addLocation(
        latitude: 40.7559,
        longitude: -73.8456,
        relevantText: 'Welcome to Shea Stadium',
    )
    ->setRelevantDate(Carbon::parse('1965-08-15 19:00'))
    ->addWifiNetwork('SheaStadium-Guest', 'welcome1965');
```

Now the pass comes forward an hour before the show when the user is near the stadium, with the Wi-Fi button visible.

Pass types that actually render the button: boarding passes (iOS 12+), event tickets (iOS 13+). Other pass types accept the field but the user won't see a button.

### What networks it supports

- WPA2/WPA3 personal (pre-shared key).
- WPA-Enterprise (username + password) is not supported.
- Hidden SSIDs work but you'll want to confirm the exact SSID string matches what the AP broadcasts.

## Security considerations

Whichever approach you pick, the password lives in the pass in plain text. Anything that exports the pass (mail attachments, screenshots, iCloud backups) exposes it. Use this for networks whose password is meant to be shared, not corporate networks or guest networks that rotate daily.

Changing the password later means [updating the pass](/docs/laravel-mobile-pass/v1/basic-usage/updating-a-pass) so the new credentials land on the user's device. Devices that have already joined keep the old password cached until they forget the network.

## Apple's reference

The underlying field for the second approach is the [`wifiAccess` semantic tag](https://developer.apple.com/documentation/walletpasses/semantictagtype/wifiaccess) in the pass's `semantics` dictionary. Apple's [Semantic Tags reference](https://developer.apple.com/documentation/walletpasses/semantictags) lists every tag the pass format supports.

The QR-code approach uses the [Wi-Fi URI format](https://en.wikipedia.org/wiki/QR_code#Joining_a_Wi-Fi_network) that originated with Android and was later adopted by iOS. It's documented by both platforms as a shortcut for joining networks.

## Try it

The [live demo](https://laravel-mobile-pass-demo-main-lgrvgq.laravel.cloud/wifi-pass) has a form where you enter an SSID and password and download a generated pass. Source: [`GenerateExampleWifiPass.php`](https://github.com/spatie/laravel-mobile-pass-demo/blob/main/app/Actions/GenerateExampleWifiPass.php) and [`WifiPassForm.php`](https://github.com/spatie/laravel-mobile-pass-demo/blob/main/app/Livewire/WifiPassForm.php).
