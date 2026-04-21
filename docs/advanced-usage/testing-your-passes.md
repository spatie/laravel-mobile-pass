---
title: Testing your passes
weight: 6
---

Testing mobile passes happens on two layers. In your test suite you mock the platform APIs and assert the payloads and workflows. For manual testing you want to actually see the pass render on a real wallet app, which is easy for Apple if you have an iPhone and trickier for Google if all your hardware is iOS.

## In your test suite

The package ships tests that demonstrate every supported pattern. The `tests/` directory is a good reference when you're writing tests against passes in your own app.

### Apple

Use `Http::fake()` around any code path that updates a pass, because the Apple notify action sends APNs push notifications over HTTP.

```php
use Illuminate\Support\Facades\Http;

Http::fake();

$mobilePass = AirlinePassBuilder::make()
    ->setOrganisationName('Spatie')
    ->setSerialNumber('abc')
    // ...
    ->save();

$mobilePass->update(['content' => [...]]);

Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://api.push.apple.com'));
```

For snapshot tests of the `.pkpass` contents, look at how `tests/Builders/Apple/AirlinePassBuilderTest.php` and `tests/Pest.php` use `toMatchMobilePassSnapshot()`. You can pull that pattern into your own test suite via the `PkPassReader` helper if you want to poke at a generated pass's JSON.

### Google

Fake every request against `walletobjects.googleapis.com` and assert on the outgoing payloads.

```php
use Illuminate\Support\Facades\Http;

Http::fake();
cache()->put('mobile-pass.google.access-token', 'test-token', 3600);

$mobilePass = EventTicketPassBuilder::make()
    ->setClass('beatles-shea-1965')
    ->setAttendeeName('Test User')
    ->save();

Http::assertSent(fn ($request) => str_contains($request->url(), '/eventTicketObject')
    && $request->method() === 'POST'
    && $request['classId'] === '3388000000000000001.beatles-shea-1965'
);
```

Priming the access token cache is important. Without it, the first request triggers a real call to Google's OAuth endpoint even under `Http::fake()`.

To test the save/remove callbacks, sign a JWT against a throwaway keypair and post it to the callback URL. See `tests/Http/Controllers/Google/HandleCallbackControllerTest.php` for the full pattern.

## Manually verifying a pass renders

No amount of mocked tests replaces seeing the pass on a real device. Grab a device or emulator you can point at.

### Apple (with an iPhone)

Build the pass, hand it to yourself, and open the resulting URL on the iPhone. Safari recognises the `.pkpass` download and prompts you to add it to Wallet.

```php
logger()->info($mobilePass->addToWalletUrl());
```

Copy the URL from your log, open it on the phone, tap Add. That's the whole flow.

### Google (without an Android device)

Google Wallet has no end-user web interface, so at some point you need an Android surface. These are the options, in order of what most issuer teams do.

1. An Android Studio emulator.

Install Android Studio, open Device Manager, and create an AVD with a Google Play system image (not "Google APIs" and not AOSP). Pixel 7 or 8 on API 33 or 34 is known-good. Sign into a Google account inside the emulator, open the Play Store, install Google Wallet.

Pass rendering and saving work fine on Play-certified emulators. Only NFC tap-to-pay is restricted, and you don't need that during development.

2. Desktop browser save, emulator view.

Open `$mobilePass->addToWalletUrl()` in desktop Chrome while signed into the same Google account you use on the emulator. You'll see Google's "Save to Google Wallet" preview page. Click Save. The pass is attached to the Google account and shows up in Google Wallet on any Android surface signed into that account, including the emulator.

That split flow is useful when the pass is part of a longer web journey you want to drive from desktop.

3. The wallet-lab-tools preview.

Paste a JWT from `$mobilePass->addToWalletUrl()` into [wallet-lab-tools.web.app](https://wallet-lab-tools.web.app/) to get an approximation of how the pass will render (colours, images, fields, barcode). It's Google-run and a good sanity check for class visuals before you touch a device.

4. Class and object round-trip.

The `find()` and `all()` methods on class builders hit Google's GET endpoints and hydrate the class back into PHP. Compare the hydrated class to what you sent:

```php
$class = EventTicketPassClass::find('beatles-shea-1965');
expect($class->getEventName())->toBe('The Beatles | Live at Shea');
```

This catches schema-level mistakes without needing a device.

5. Remote real devices.

BrowserStack App Live and LambdaTest Real Device Cloud rent real Android hardware with interactive VNC sessions. Sign into a Google account, install Wallet, test manually. Works, but it's pricey. Use when the emulator isn't enough.

## Integration testing against a real issuer

For the last mile of confidence, create a separate staging issuer in the Google Pay & Wallet Business Console. Set `MOBILE_PASS_GOOGLE_ISSUER_ID` to it in staging and CI. Your integration tests can then `insert` classes and objects against the real API, `get` them back, and assert the round-trip.

Google's Wallet REST API has no dry-run mode, so this is the only way to catch shape mistakes that static validation misses. The package has no opinion about whether you use a staging issuer, so it's up to you to set one up.

## Queueing in tests

If you've opted into the queue (see [Queueing update pushes](queueing-update-pushes)), remember to call `Queue::fake()` in your tests so update pushes don't actually ship. Assertions then look like:

```php
use Illuminate\Support\Facades\Queue;
use Spatie\LaravelMobilePass\Jobs\PushPassUpdateJob;

Queue::fake();

$mobilePass->update(['content' => [...]]);

Queue::assertPushed(PushPassUpdateJob::class);
```
