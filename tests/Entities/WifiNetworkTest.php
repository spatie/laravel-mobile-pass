<?php

use Spatie\LaravelMobilePass\Entities\WifiNetwork;

it('builds a basic wifi network object', function () {
    $network = WifiNetwork::make(
        ssid: 'Spatie HQ',
        password: 'super-secret-password'
    );

    expect($network->toArray())->toBe([
        'ssid' => 'Spatie HQ',
        'password' => 'super-secret-password',
    ]);
});
