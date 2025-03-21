<?php

namespace Spatie\LaravelMobilePass\Tests\Http;

use Illuminate\Support\Facades\Event;
use Spatie\LaravelMobilePass\Events\MobilePassUnregisteredEvent;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

it('soft deletes the registration', function () {
    $registration = MobilePassRegistration::factory()->create();

    $this
        ->withoutMiddleware()
        ->deleteJson(route('mobile-pass.unregister-device', [
            'passSerial' => $registration->pass->getKey(),
            'deviceId' => $registration->device->getKey(),
            'passTypeId' => $registration->pass_type_id,
        ]))
        ->assertSuccessful();

    $this->assertSoftDeleted(MobilePassRegistration::class, [
        'device_id' => $registration->device->getKey(),
        'pass_serial' => $registration->pass->getKey(),
        'pass_type_id' => $registration->pass_type_id,
    ]);
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

it('fires an event', function () {
    $registration = MobilePassRegistration::factory()->create();

    Event::fake([
        MobilePassUnregisteredEvent::class,
    ]);

    $this
        ->withoutMiddleware()
        ->deleteJson(route('mobile-pass.unregister-device', [
            'passSerial' => $registration->pass->getKey(),
            'deviceId' => $registration->device->getKey(),
            'passTypeId' => $registration->pass_type_id,
        ]))
        ->assertSuccessful();

    Event::assertDispatched(function (MobilePassUnregisteredEvent $event) use ($registration) {
        return $event->registration->getKey() === $registration->getKey();
    });
});
