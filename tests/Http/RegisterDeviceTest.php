<?php

namespace Spatie\LaravelMobilePass\Tests\Feature;

use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

it('stores the registration', function () {
    $pass = MobilePass::create();

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

    $this->assertModelExists(MobilePassRegistration::class, [
        'device_id' => '12345',
        'pass_serial' => $pass->getKey(),
        'pass_type_id' => 'pass.com.example',
        'push_token' => '12345',
    ]);
});

it('doesnt trigger a change notification to Apple', function () {
    $pass = MobilePass::create();

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
    $pass = MobilePass::create();
    $pass->registrations()->create([
        'device_id' => '12345',
        'pass_type_id' => 'pass.com.example',
        'pass_serial' => $pass->getKey(),
        'push_token' => '12345',
    ]);

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.register-device', [
            'passSerial' => $pass->getKey(),
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
        ]), [
            'pushToken' => '12345',
        ])
        ->assertStatus(200);

    $this->assertSame(1, MobilePassRegistration::count());
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
