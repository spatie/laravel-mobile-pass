---
title: Installation & setup
weight: 4
---

Pull the package in with Composer:

```bash
composer require spatie/laravel-mobile-pass
```

## Getting credentials from Apple and Google

Each platform wants its own credentials before you can issue passes. Follow the walkthrough for whichever platforms you plan to support:

- [Getting credentials from Apple](/docs/laravel-mobile-pass/v1/apple-wallet/getting-credentials-from-apple)
- [Getting credentials from Google](/docs/laravel-mobile-pass/v1/google-wallet/getting-credentials-from-google)

Each guide walks you through what to register, which keys to download, and the environment variables to set.

## Publishing the config file

You can publish the `laravel-mobile-pass` config if you want to tweak it:

```bash
php artisan vendor:publish --tag="mobile-pass-config"
```

Here's what the published file looks like:

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

The package keeps track of generated passes, Apple device registrations, and Google save/remove events in your database. Publish and run the included migration:

```bash
php artisan vendor:publish --tag="mobile-pass-migrations"
php artisan migrate
```

The published `create_mobile_pass_tables` migration creates four tables: `mobile_passes`, `apple_mobile_pass_devices`, `apple_mobile_pass_registrations`, and `mobile_pass_google_events`.

## Registering the routes

Apple needs to reach your app to register devices and log errors. To wire up the routes it calls into, drop the `mobilePass` macro into your routes file:

```php
// in your routes file
Route::mobilePass();
```
