<?php

use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;

it('omits webServiceURL when webservice.host is not configured', function () {
    config()->set('mobile-pass.apple.webservice.host', null);

    $data = CouponPassBuilder::make()
        ->setOrganisationName('Acme')
        ->setSerialNumber('abc')
        ->setDescription('Coupon')
        ->data();

    expect($data)->not->toHaveKey('webServiceURL');
});

it('appends /passkit to the configured host', function () {
    config()->set('mobile-pass.apple.webservice.host', 'https://example.test');

    $data = CouponPassBuilder::make()
        ->setOrganisationName('Acme')
        ->setSerialNumber('abc')
        ->setDescription('Coupon')
        ->data();

    expect($data['webServiceURL'])->toBe('https://example.test/passkit');
});

it('strips a trailing slash from the configured host', function () {
    config()->set('mobile-pass.apple.webservice.host', 'https://example.test/');

    $data = CouponPassBuilder::make()
        ->setOrganisationName('Acme')
        ->setSerialNumber('abc')
        ->setDescription('Coupon')
        ->data();

    expect($data['webServiceURL'])->toBe('https://example.test/passkit');
});

it('preserves a custom path in the configured host (for users with a route prefix)', function () {
    config()->set('mobile-pass.apple.webservice.host', 'https://example.test/api');

    $data = CouponPassBuilder::make()
        ->setOrganisationName('Acme')
        ->setSerialNumber('abc')
        ->setDescription('Coupon')
        ->data();

    expect($data['webServiceURL'])->toBe('https://example.test/api/passkit');
});
