<?php

return [
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
