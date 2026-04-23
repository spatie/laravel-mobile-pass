<?php

namespace Spatie\LaravelMobilePass\Tests\Http;

use Illuminate\Support\Facades\Event;
use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Events\MobilePassAdded;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;

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

    $this->assertModelExists(AppleMobilePassDevice::class, [
        'device_id' => '12345',
        'pass_serial' => $pass->getKey(),
        'push_token' => '12345',
    ]);

    $this->assertModelExists(AppleMobilePassRegistration::class, [
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
    $registration = AppleMobilePassRegistration::factory()->create();

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

    $this->assertSame(1, AppleMobilePassRegistration::count());
    $this->assertSame(1, AppleMobilePassDevice::count());
});

it('fires MobilePassAdded when a new registration is created', function () {
    Event::fake([MobilePassAdded::class]);

    $pass = MobilePass::factory()->create();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.register-device', [
            'passSerial' => $pass->getKey(),
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
        ]), ['pushToken' => '12345']);

    Event::assertDispatched(
        fn (MobilePassAdded $event) => $event->mobilePass->is($pass),
    );
});

it('does not fire MobilePassAdded when the same device re-registers', function () {
    $registration = AppleMobilePassRegistration::factory()->create();

    Event::fake([MobilePassAdded::class]);

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.register-device', [
            'passSerial' => $registration->pass->getKey(),
            'deviceId' => $registration->device->getKey(),
            'passTypeId' => 'pass.com.example',
        ]), ['pushToken' => '12345']);

    Event::assertNotDispatched(MobilePassAdded::class);
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
