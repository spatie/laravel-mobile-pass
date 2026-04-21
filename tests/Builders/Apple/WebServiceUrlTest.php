<?php

use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;

it('omits webServiceURL when webservice.host is not configured', function () {
    config()->set('mobile-pass.apple.webservice.host', null);

    $data = CouponPassBuilder::make()
        ->setOrganisationName('Acme')
        ->setSerialNumber('abc')
        ->setDescription('Coupon')
        ->data();

    expect($data)->not->toHaveKey('webServiceURL');
});

it('throws when the host is not HTTPS', function () {
    // Apple rejects passes whose webServiceURL is not served over HTTPS,
    // so we throw early rather than produce a silently-broken pass.
    config()->set('mobile-pass.apple.webservice.host', 'http://example.test');

    CouponPassBuilder::make()
        ->setOrganisationName('Acme')
        ->setSerialNumber('abc')
        ->setDescription('Coupon')
        ->data();
})->throws(InvalidConfig::class, 'must use HTTPS');

it('appends /passkit to the configured host', function () {
    config()->set('mobile-pass.apple.webservice.host', 'https://example.test');

    $data = CouponPassBuilder::make()
        ->setOrganisationName('Acme')
        ->setSerialNumber('abc')
        ->setDescription('Coupon')
        ->data();

    expect($data['webServiceURL'])->toBe('https://example.test/passkit');
});

it('strips a trailing slash from the configured https host', function () {
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
