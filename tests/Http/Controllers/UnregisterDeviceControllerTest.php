<?php

namespace Spatie\LaravelMobilePass\Tests\Http;

use Spatie\LaravelMobilePass\Models\MobilePassDevice;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

it('deletes the registration', function () {
    $registration = MobilePassRegistration::factory()->create();

    $this
        ->withoutMiddleware()
        ->deleteJson(route('mobile-pass.unregister-device', [
            'passSerial' => $registration->pass->getKey(),
            'deviceId' => $registration->device->getKey(),
            'passTypeId' => $registration->pass_type_id,
        ]))
        ->assertSuccessful();

    expect($registration->fresh())->toBeNull();
});

it('doesnt delete the device', function () {
    $registration = MobilePassRegistration::factory()->create();

    $this
        ->withoutMiddleware()
        ->deleteJson(route('mobile-pass.unregister-device', [
            'passSerial' => $registration->pass->getKey(),
            'deviceId' => $registration->device->getKey(),
            'passTypeId' => $registration->pass_type_id,
        ]))
        ->assertSuccessful();

    $this->assertModelExists(MobilePassDevice::class, [
        'device_id' => $registration->device->getKey(),
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
