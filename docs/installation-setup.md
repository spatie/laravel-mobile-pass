---
title: Installation & setup
weight: 4
---

You can install the package via composer:

```bash
composer require spatie/laravel-mobile-pass
```

## Register your application at Apple

You'll find the necessary information [on this page in our docs](https://spatie.be/docs/laravel-mobile-pass/v1/basic-usage/getting-credentials-from-apple).

## Google side setup

If you also want to publish passes to Google Wallet, you'll need a Google Cloud service account and an issuer ID from the Google Pay & Wallet Business Console. Follow the walkthrough on [Getting credentials from Google](https://spatie.be/docs/laravel-mobile-pass/v1/basic-usage/getting-credentials-from-google).

Once you have the credentials, set these environment variables:

```bash
MOBILE_PASS_GOOGLE_ISSUER_ID=
MOBILE_PASS_GOOGLE_KEY_PATH=
# or MOBILE_PASS_GOOGLE_KEY_CONTENTS, or MOBILE_PASS_GOOGLE_KEY_BASE64
MOBILE_PASS_GOOGLE_CALLBACK_SIGNING_KEY=
```

## Publishing the config file

Optionally, you can publish the `laravel-mobile-pass` config file with this command.

```bash
php artisan vendor:publish --tag="mobile-pass-config"
```

This is the content of the published config file:

```php
return [
    /*
     * Read the "Getting credentials from Apple" section in the documentation
     * to learn how to get these values.
     */
    'apple' => [
        'organisation_name' => env('MOBILE_PASS_APPLE_ORGANISATION_NAME'),
        'type_identifier' => env('MOBILE_PASS_APPLE_TYPE_IDENTIFIER'),
        'team_identifier' => env('MOBILE_PASS_APPLE_TEAM_IDENTIFIER'),

        /*
         * These values are used to ensure secure communication with Apple.
         */
        'apple_push_base_url' => 'https://api.push.apple.com/3/device',
        'certificate_path' => env('MOBILE_PASS_APPLE_CERTIFICATE_PATH'),
        'certificate_contents' => env('MOBILE_PASS_APPLE_CERTIFICATE_CONTENTS'),
        'certificate_password' => env('MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD'),
        'webservice' => [
            'secret' => env('MOBILE_PASS_APPLE_WEBSERVICE_SECRET'),
            'host' => env('MOBILE_PASS_APPLE_WEBSERVICE_HOST'),
        ],
    ],

    /*
     * Read the "Getting credentials from Google" section in the documentation
     * to learn how to get these values.
     */
    'google' => [
        'issuer_id' => env('MOBILE_PASS_GOOGLE_ISSUER_ID'),

        'service_account_key_base64' => env('MOBILE_PASS_GOOGLE_KEY_BASE64'),
        'service_account_key_contents' => env('MOBILE_PASS_GOOGLE_KEY_CONTENTS'),
        'service_account_key_path' => env('MOBILE_PASS_GOOGLE_KEY_PATH'),

        'origins' => [env('APP_URL')],

        'api_base_url' => env(
            'MOBILE_PASS_GOOGLE_API_BASE_URL',
            'https://walletobjects.googleapis.com/walletobjects/v1'
        ),

        'callback_signing_key' => env('MOBILE_PASS_GOOGLE_CALLBACK_SIGNING_KEY'),
    ],

    /*
     * The actions perform core tasks offered by this package. You can customize the behaviour
     * by creating your own action class that extend the one that ships with the package.
     */
    'actions' => [
        'handle_google_callback' => Spatie\LaravelMobilePass\Actions\Google\HandleGoogleCallbackAction::class,
        'notify_apple_of_pass_update' => Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction::class,
        'notify_google_of_pass_update' => Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction::class,
        'register_device' => Spatie\LaravelMobilePass\Actions\Apple\RegisterDeviceAction::class,
        'unregister_device' => Spatie\LaravelMobilePass\Actions\Apple\UnregisterDeviceAction::class,
    ],

    /*
     * These are the models used by this package. You can replace them with
     * your own models by extending the ones that ship with the package.
     */
    'models' => [
        'mobile_pass' => Spatie\LaravelMobilePass\Models\MobilePass::class,
        'apple_mobile_pass_registration' => Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration::class,
        'apple_mobile_pass_device' => Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice::class,
        'google_mobile_pass_event' => Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent::class,
    ],

    /*
     * The builders are responsible for creating the pass that will be stored in the `mobile_passes` table.
     */
    'builders' => [
        'apple' => [
            'airline' => Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder::class,
            'boarding' => Spatie\LaravelMobilePass\Builders\Apple\BoardingPassBuilder::class,
            'coupon' => Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder::class,
            'event_ticket' => Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder::class,
            'generic' => Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder::class,
            'store_card' => Spatie\LaravelMobilePass\Builders\Apple\StoreCardPassBuilder::class,
        ],
        'google' => [
            'boarding' => Spatie\LaravelMobilePass\Builders\Google\BoardingPassBuilder::class,
            'event_ticket' => Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder::class,
            'generic' => Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder::class,
            'loyalty' => Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassBuilder::class,
            'offer' => Spatie\LaravelMobilePass\Builders\Google\OfferPassBuilder::class,
        ],
    ],

    /*
     * The queue connection and name used for pushing pass updates to the Apple and Google
     * wallet APIs. When the connection is `null`, updates will run synchronously.
     */
    'queue' => [
        'connection' => env('MOBILE_PASS_QUEUE_CONNECTION'),
        'name' => env('MOBILE_PASS_QUEUE_NAME', 'default'),
    ],
];
```

## Migrating the database

The package uses the database to track generated passes, Apple device registrations, and Google save/remove events. Publish and run the included migrations:

```bash
php artisan vendor:publish --tag="mobile-pass-migrations"
php artisan migrate
```

Two migrations are published:

- `create_mobile_pass_tables` creates the `mobile_passes`, `apple_mobile_pass_devices`, and `apple_mobile_pass_registrations` tables.
- `add_google_wallet_support` adds the `expired_at` column to `mobile_passes` (used by `$pass->expire()`) and creates the `mobile_pass_google_events` table (used by save and remove callbacks).

### Upgrading an existing install

If you already had `create_mobile_pass_tables` applied before upgrading to the Google-aware release, only the second migration runs. The `expired_at` column is added conditionally (it checks if the column exists), so repeat migrations are safe.

## Registering the routes

The package can receive device registration requests and logs from Apple. To set up the necessary routes, call the `mobilePass` macro in your routes file.

```php
// in your routes file

Route::mobilePass();
```
