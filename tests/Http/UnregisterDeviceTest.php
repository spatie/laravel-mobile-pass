<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

it('soft deletes the registration', function () {
    $pass = MobilePass::create();
    $pass->registrations()->create([
        'device_id' => '12345',
        'pass_type_id' => 'pass.com.example',
        'pass_serial' => $pass->getKey(),
        'push_token' => '12345',
    ]);

    $this
        ->withoutMiddleware()
        ->deleteJson(route('mobile-pass.unregister-device', [
            'passSerial' => $pass->getKey(),
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();

    $this->assertSoftDeleted(MobilePassRegistration::class, [
        'device_id' => '12345',
        'pass_serial' => $pass->getKey(),
        'pass_type_id' => 'pass.com.example',
        'push_token' => '12345',
    ]);
});

it('returns success even if the registration wasnt found', function () {
    $this
        ->withoutMiddleware()
        ->deleteJson(route('mobile-pass.unregister-device', [
            'passSerial' => '12345',
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();
});
