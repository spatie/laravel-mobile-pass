<?php

namespace Spatie\LaravelMobilePass\Tests\Http;

use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;

it('lets Apple register, list, look up, and unregister a pass that uses a custom non-UUID serial', function () {
    $customSerial = 'BTL-SHEA-0042';

    $pass = CouponPassBuilder::make()
        ->setOrganizationName('Acme')
        ->setDescription('Boston Tea Leaves Loyalty')
        ->setSerialNumber($customSerial)
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    expect($pass->pass_serial)->toBe($customSerial);
    expect($pass->content['serialNumber'])->toBe($customSerial);
    expect($pass->getKey())->not->toBe($customSerial);

    // Apple registers the device for this serial
    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.register-device', [
            'passSerial' => $customSerial,
            'deviceId' => 'device-1',
            'passTypeId' => 'pass.com.example',
        ]), ['pushToken' => 'token-1'])
        ->assertCreated();

    $this->assertModelExists(AppleMobilePassRegistration::class, [
        'device_id' => 'device-1',
        'mobile_pass_id' => $pass->getKey(),
        'pass_type_id' => 'pass.com.example',
    ]);

    // Apple asks for the list of serials it should poll for updates
    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.get-associated-serials-for-device', [
            'deviceId' => 'device-1',
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful()
        ->assertJson(['serialNumbers' => [$customSerial]]);

    // Apple checks for an updated pass using the serial it received above
    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.check-for-updates', [
            'passSerial' => $customSerial,
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();

    // Apple unregisters the device for this serial
    $this
        ->withoutMiddleware()
        ->deleteJson(route('mobile-pass.unregister-device', [
            'passSerial' => $customSerial,
            'deviceId' => 'device-1',
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();

    expect(AppleMobilePassRegistration::count())->toBe(0);
});

it('falls back to a UUID pass_serial when no serial is set', function () {
    $pass = CouponPassBuilder::make()
        ->setOrganizationName('Acme')
        ->setDescription('Default-serial Coupon')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    expect($pass->pass_serial)->toBe($pass->content['serialNumber']);
    expect($pass->getKey())->not->toBe($pass->pass_serial);

    $this
        ->withoutMiddleware()
        ->getJson(route('mobile-pass.check-for-updates', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]))
        ->assertSuccessful();
});
