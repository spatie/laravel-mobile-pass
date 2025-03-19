<?php

return [
    /*
     * The actions perform core tasks offered by this package. You can customize the behaviour
     * by creating your own action class that extend the one that ships with the package.
     */
    'actions' => [
        'notify_apple_of_pass_update' => Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction::class,
    ],

    /*
     * These are the models used by this package. You can replace them with
     * your own models by extending the ones that ship with the package.
     */
    'models' => [
        'mobile_pass' => Spatie\LaravelMobilePass\Models\MobilePass::class,
        'mobile_pass_registration' => Spatie\LaravelMobilePass\Models\MobilePassRegistration::class,
    ],

    'organisation_name' => env('MOBILE_PASS_ORGANISATION_NAME', 'Spatie'),
    'type_identifier' => env('MOBILE_PASS_TYPE_IDENTIFIER'),
    'team_identifier' => env('MOBILE_PASS_TEAM_IDENTIFIER'),

    'apple' => [
        'apple_push_base_url' => 'https://api.push.apple.com/3/device',
        'certificate_path' => env('MOBILE_PASS_APPLE_CERTIFICATE_PATH'),
        'certificate_contents' => env('MOBILE_PASS_APPLE_CERTIFICATE_CONTENTS'),
        'certificate_password' => env('MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD'),
    ],
];
