<?php

use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Actions\Apple\RegisterDeviceAction;
use Spatie\LaravelMobilePass\Actions\Apple\UnregisterDeviceAction;
use Spatie\LaravelMobilePass\Actions\Google\HandleGoogleCallbackAction;
use Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;

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
        * Point certificate at a file on disk, or inline the base64-encoded
        * .p12 contents through MOBILE_PASS_APPLE_CERTIFICATE. Exactly one
        * of the two needs to be set.
        */
        'certificate' => env('MOBILE_PASS_APPLE_CERTIFICATE'),
        'certificate_path' => env('MOBILE_PASS_APPLE_CERTIFICATE_PATH'),
        'certificate_password' => env('MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD'),

        'apple_push_base_url' => 'https://api.push.apple.com/3/device',
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

        /*
        * Point service_account_key at a file on disk, or inline the JSON
        * contents (raw or base64-encoded) through MOBILE_PASS_GOOGLE_KEY.
        * Exactly one of the two needs to be set.
        */
        'service_account_key' => env('MOBILE_PASS_GOOGLE_KEY'),
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
        'handle_google_callback' => HandleGoogleCallbackAction::class,
        'notify_apple_of_pass_update' => NotifyAppleOfPassUpdateAction::class,
        'notify_google_of_pass_update' => NotifyGoogleOfPassUpdateAction::class,
        'register_device' => RegisterDeviceAction::class,
        'unregister_device' => UnregisterDeviceAction::class,
    ],

    /*
    * These are the models used by this package. You can replace them with
    * your own models by extending the ones that ship with the package.
    */
    'models' => [
        'mobile_pass' => MobilePass::class,
        'apple_mobile_pass_registration' => AppleMobilePassRegistration::class,
        'apple_mobile_pass_device' => AppleMobilePassDevice::class,
        'google_mobile_pass_event' => GoogleMobilePassEvent::class,
    ],

    /*
    * Register custom pass builders here. Built-in builders are registered
    * automatically — only add entries for builders you have authored yourself.
    * The array is keyed by the builder's snake_case name.
    */
    'builders' => [
        'apple' => [
            // 'my_custom_apple_pass' => MyCustomApplePassBuilder::class,
        ],
        'google' => [
            // 'my_custom_google_pass' => MyCustomGooglePassBuilder::class,
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
