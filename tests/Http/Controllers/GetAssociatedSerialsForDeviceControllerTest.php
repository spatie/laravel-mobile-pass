<?php

namespace Spatie\LaravelMobilePass\Tests\Http;

use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\TestTime\TestTime;

it('returns pass serials associated with a device', function () {
    $registration = AppleMobilePassRegistration::factory()->create();

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.get-associated-serials-for-device', [
            'deviceId' => $registration->device_id,
            'passTypeId' => $registration->pass_type_id,
            'passesUpdatedSince' => now()
                ->subMinutes(15)
                ->toIso8601ZuluString(),
        ]))
        ->assertSuccessful()
        ->assertJson([
            'serialNumbers' => [
                $registration->pass->getKey(),
            ],
        ]);
});

it('returns the timestamp of the last update', function () {
    $registration = AppleMobilePassRegistration::factory()->create();

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.get-associated-serials-for-device', [
            'deviceId' => $registration->device_id,
            'passTypeId' => $registration->pass_type_id,
            'passesUpdatedSince' => now()
                ->subMinutes(15)
                ->toIso8601ZuluString(),
        ]))
        ->assertSuccessful()
        ->assertJson([
            'lastUpdated' => now()->toIso8601ZuluString(),
        ]);
});

it('only returns passes that have been updated since the given timestamp', function () {
    $device = AppleMobilePassDevice::factory()->create();

    $firstPass = MobilePass::factory()
        ->hasRegistrationForDevice($device)
        ->create();

    TestTime::addMinutes(15);

    $secondPass = MobilePass::factory()
        ->hasRegistrationForDevice($device)
        ->create();

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.get-associated-serials-for-device', [
            'deviceId' => $device->getKey(),
            'passTypeId' => $device->registrations()->first()->pass_type_id,
            'passesUpdatedSince' => now()
                ->copy()
                ->subMinutes(5)
                ->toIso8601ZuluString(),
        ]))
        ->assertSuccessful()
        ->assertJson([
            'serialNumbers' => [
                $secondPass->getKey(),
            ],
        ]);
});

it('returns the timestamp of the most recently updated pass', function () {
    $device = AppleMobilePassDevice::factory()->create();

    $firstPass = MobilePass::factory()
        ->hasRegistrationForDevice($device)
        ->create();

    TestTime::addMinutes(15);

    $secondPass = MobilePass::factory()
        ->hasRegistrationForDevice($device)
        ->create();

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.get-associated-serials-for-device', [
            'deviceId' => $device->getKey(),
            'passTypeId' => $device->registrations()->first()->pass_type_id,
            'passesUpdatedSince' => now()
                ->copy()
                ->subMinutes(5)
                ->toIso8601ZuluString(),
        ]))
        ->assertSuccessful()
        ->assertJson([
            'lastUpdated' => now()->toIso8601ZuluString(),
        ]);
});

it('returns 204 if no passes available', function () {
    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.get-associated-serials-for-device', [
            'deviceId' => '12345',
            'passTypeId' => '12345',
            'passesUpdatedSince' => now()
                ->copy()
                ->subMinutes(5)
                ->toIso8601ZuluString(),
        ]))
        ->assertNoContent();
});

it('returns 204 if no passes have been updated since the given timestamp', function () {
    $registration = AppleMobilePassRegistration::factory()->create();

    TestTime::addMinutes(5);

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.get-associated-serials-for-device', [
            'deviceId' => $registration->device_id,
            'passTypeId' => $registration->pass_type_id,
            'passesUpdatedSince' => now()->toIso8601ZuluString(),
        ]))
        ->assertNoContent();
});
