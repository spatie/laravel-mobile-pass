<?php

return [
    /*
     * The actions perform core tasks offered by this package. You can customize the behaviour
     * by creating your own action class that extend the one that ships with the package.
     */
    'actions' => [
        'notify_apple_of_pass_update' => \Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction::class,
        'register_device' => \Spatie\LaravelMobilePass\Actions\Apple\RegisterDeviceAction::class,
        'unregister_device' => \Spatie\LaravelMobilePass\Actions\Apple\UnregisterDeviceAction::class,
    ],

    /*
     * These are the models used by this package. You can replace them with
     * your own models by extending the ones that ship with the package.
     */
    'models' => [
        'mobile_pass' => Spatie\LaravelMobilePass\Models\MobilePass::class,
        'apple_mobile_pass_registration' => \Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration::class,
        'apple_mobile_pass_device' => \Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice::class,
    ],

    /*
     * The builders are responsible for creating the pass that will be stored in the `mobile_passes` table.
     */
    'builders' => [
        'apple' => [
            'airline' => Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder::class,
            'boarding' => Spatie\LaravelMobilePass\Builders\Apple\BoardingPassBuilder::class,
            'coupon' => Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder::class,
            'generic' => Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder::class,
            'store_card' => Spatie\LaravelMobilePass\Builders\Apple\StoreCardPassBuilder::class,
        ],
    ],


    /*
     * The values are used to ensure a secure communication with Apple.
     */
    'apple' => [
        'organisation_name' => env('MOBILE_PASS_APPLE_ORGANISATION_NAME', 'Spatie'),
        'type_identifier' => env('MOBILE_PASS_APPLE_TYPE_IDENTIFIER'),
        'team_identifier' => env('MOBILE_PASS_APPLE_TEAM_IDENTIFIER'),
        'apple_push_base_url' => 'https://api.push.apple.com/3/device',
        'certificate_path' => env('MOBILE_PASS_APPLE_CERTIFICATE_PATH'),
        'certificate_contents' => env('MOBILE_PASS_APPLE_CERTIFICATE_CONTENTS'),
        'certificate_password' => env('MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD'),
        'webservice' => [
            'secret' => env('MOBILE_PASS_APPLE_WEBSERVICE_SECRET'),
            'host' => env('MOBILE_PASS_APPLE_WEBSERVICE_HOST'),
        ],
    ],
];
