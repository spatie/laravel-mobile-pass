<?php

namespace Spatie\LaravelMobilePass\Tests\Http;

use Illuminate\Support\Facades\Event;
use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Events\MobilePassRegisteredEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassDevice;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

it('stores the registration', function () {
    $pass = MobilePass::factory()->create();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.register-device', [
            'passSerial' => $pass->getKey(),
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
        ]), [
            'pushToken' => '12345',
        ])
        ->assertCreated();

    $this->assertModelExists(MobilePassDevice::class, [
        'device_id' => '12345',
        'pass_serial' => $pass->getKey(),
        'push_token' => '12345',
    ]);

    $this->assertModelExists(MobilePassRegistration::class, [
        'device_id' => '12345',
        'pass_serial' => $pass->getKey(),
        'pass_type_id' => 'pass.com.example',
    ]);
});

it('doesnt trigger a change notification to Apple', function () {
    $pass = MobilePass::factory()->create();

    $this
        ->mock(NotifyAppleOfPassUpdateAction::class)
        ->makePartial()
        ->shouldNotReceive('execute');

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.register-device', [
            'passSerial' => $pass->getKey(),
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
        ]), [
            'pushToken' => '12345',
        ]);
});

it('doesnt create duplicate entries for the same device', function () {
    $registration = MobilePassRegistration::factory()->create();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.register-device', [
            'passSerial' => $registration->pass->getKey(),
            'deviceId' => $registration->device->getKey(),
            'passTypeId' => 'pass.com.example',
        ]), [
            'pushToken' => '12345',
        ])
        ->assertStatus(200);

    $this->assertSame(1, MobilePassRegistration::count());
    $this->assertSame(1, MobilePassDevice::count());
});

it('returns 404 if the pass doesnt exist', function () {
    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.register-device', [
            'passSerial' => '123',
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
        ]), [
            'pushToken' => '12345',
        ])
        ->assertNotFound();
});
