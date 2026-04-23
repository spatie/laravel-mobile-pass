---
title: Handling errors
weight: 1
---

The package throws a handful of typed exceptions so you can recover from the common failure modes without parsing error messages.

## Validation errors

Every builder runs a Laravel validator before it hands a pass off to Apple or Google. If a required field is missing, or a value has the wrong shape, `save()` throws a `Spatie\LaravelMobilePass\Exceptions\InvalidPass`.

```php
use Spatie\LaravelMobilePass\Exceptions\InvalidPass;

try {
    EventTicketPassBuilder::make()
        ->setOrganizationName('Fab Four Promotions')
        // setSerialNumber omitted
        ->setDescription('The Beatles at Shea Stadium')
        ->save();
} catch (InvalidPass $exception) {
    report($exception);
}
```

`InvalidPass` extends Laravel's `Illuminate\Validation\ValidationException`, so a couple of things still work out of the box:

- `$exception->errors()` gives you the field-by-field reasons.
- Catching `ValidationException` instead of `InvalidPass` still works, if you prefer the broader type.
- Thrown from a controller method, Laravel's default exception handler still renders a 422 JSON response with the errors.

## Configuration errors

`Spatie\LaravelMobilePass\Exceptions\InvalidConfig` is thrown when the `mobile-pass` config file is missing something the package needs. The most common reasons:

- `InvalidConfig::missingGoogleCredentials()`: no Google service account key is configured. Set either `MOBILE_PASS_GOOGLE_KEY` (raw JSON or base64-encoded JSON) or `MOBILE_PASS_GOOGLE_KEY_PATH`.
- `InvalidConfig::webserviceHostMustBeHttps($host)`: `mobile-pass.apple.webservice.host` is set to a non-HTTPS URL. Apple rejects passes whose `webServiceURL` isn't HTTPS. Leave the value empty for local development over `http://`.
- `InvalidConfig::passBuilderNotRegistered()` and `InvalidConfig::invalidPassBuilderClass()`: a builder you're referencing isn't in the `builders` config key, or doesn't extend the expected base class.

These surface at runtime the first time the package tries to use the misconfigured value. Catch them in a `Handler::register()` call if you want to render a friendlier error page for developers.

## Platform mismatches

The `Spatie\LaravelMobilePass\Exceptions\PlatformDoesntSupport` exception fires when you call a method that doesn't make sense for the pass's platform.

Calling `$mobilePass->updateField(...)` on a Google pass throws `PlatformDoesntSupport::cannotUpdateFields(Platform::Google)`. Use the Google builder's `content`-patching flow instead (see [Updating a pass](/docs/laravel-mobile-pass/v1/basic-usage/updating-a-pass)).

It's the kind of mistake you want to catch during development. Let it bubble to your exception handler in production; it indicates a bug in your code, not a user error.

## Download errors

Calling `$mobilePass->download()` on a Google pass throws `Spatie\LaravelMobilePass\Exceptions\CannotDownload::wrongPlatform($mobilePass)`. The same exception fires if a Google pass is requested through Apple's download route. Google passes aren't files; they live on Google's servers and users reach them through a `pay.google.com` save URL.

## Google Wallet API errors

Every call to Google's Wallet API (creating a Class, creating an Object, fetching or retiring) goes through a typed failure. `Spatie\LaravelMobilePass\Exceptions\GoogleWalletApiError` carries the original HTTP response so you can inspect what Google actually said:

```php
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletApiError;

try {
    EventTicketPassClass::make('duplicate-id')
        ->setIssuerName('...')
        ->save();
} catch (GoogleWalletApiError $exception) {
    logger()->error('Google Wallet rejected the request', [
        'status' => $exception->status,
        'body' => $exception->body,
        'endpoint' => $exception->endpoint,
    ]);
}
```

The common causes are duplicate Class IDs, malformed payloads, or an expired service account key. Google's error bodies are JSON; decode them to read the field-level reasons.

## APNs push failures

The package notifies Apple Wallet of pass updates over APNs. If APNs can't be reached, or Apple rejects the push, the HTTP client throws a standard `Illuminate\Http\Client\RequestException`. The `PushPassUpdateJob` will retry according to your queue's retry policy when dispatched to a queue; when running synchronously, the exception bubbles up on the web request that triggered the update.

If APNs rejections are a persistent pattern (expired cert, revoked token), the underlying HTTP status code in the response body is where you'll see what Apple thinks.

## When the pass itself is malformed

`.pkpass` generation uses the underlying `pkpass/pkpass` library. Signing failures (bad certificate path, wrong password, expired cert) surface as `Exception` from that library with messages like `Invalid certificate file. Make sure you have a P12 certificate that also contains a private key`. Check your `MOBILE_PASS_APPLE_CERTIFICATE_PATH` and `MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD` values, and confirm the cert hasn't expired.
