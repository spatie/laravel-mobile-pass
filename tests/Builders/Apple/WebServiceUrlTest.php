<?php

use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;

function buildCouponPassData(): array
{
    return CouponPassBuilder::make()
        ->setOrganizationName('Acme')
        ->setSerialNumber('abc')
        ->setDescription('Coupon')
        ->data();
}

it('omits webServiceURL and authenticationToken when neither the host nor the secret is configured', function () {
    config()->set('mobile-pass.apple.webservice.host', null);
    config()->set('mobile-pass.apple.webservice.secret', null);
    config()->set('app.url', 'http://localhost');

    $data = buildCouponPassData();

    expect($data)->not->toHaveKeys(['webServiceURL', 'authenticationToken']);
});

it('omits both keys when only the host is configured', function () {
    // Apple rejects passes that carry a webServiceURL without an
    // authenticationToken, so neither key may end up in pass.json.
    config()->set('mobile-pass.apple.webservice.host', 'https://example.test');
    config()->set('mobile-pass.apple.webservice.secret', null);

    $data = buildCouponPassData();

    expect($data)->not->toHaveKeys(['webServiceURL', 'authenticationToken']);
});

it('omits both keys when only the secret is configured but no HTTPS URL resolves', function () {
    config()->set('mobile-pass.apple.webservice.host', null);
    config()->set('mobile-pass.apple.webservice.secret', 'a-very-secret-token-0123456789');
    config()->set('app.url', 'http://localhost');

    $data = buildCouponPassData();

    expect($data)->not->toHaveKeys(['webServiceURL', 'authenticationToken']);
});

it('throws when the host is not HTTPS', function () {
    // Apple rejects passes whose webServiceURL is not served over HTTPS,
    // so we throw early rather than produce a silently-broken pass.
    config()->set('mobile-pass.apple.webservice.host', 'http://example.test');
    config()->set('mobile-pass.apple.webservice.secret', 'a-very-secret-token-0123456789');

    buildCouponPassData();
})->throws(InvalidConfig::class, 'must use HTTPS');

it('appends /passkit to the configured host', function () {
    config()->set('mobile-pass.apple.webservice.host', 'https://example.test');
    config()->set('mobile-pass.apple.webservice.secret', 'a-very-secret-token-0123456789');

    $data = buildCouponPassData();

    expect($data['webServiceURL'])->toBe('https://example.test/passkit')
        ->and($data['authenticationToken'])->toBe('a-very-secret-token-0123456789');
});

it('strips a trailing slash from the configured https host', function () {
    config()->set('mobile-pass.apple.webservice.host', 'https://example.test/');
    config()->set('mobile-pass.apple.webservice.secret', 'a-very-secret-token-0123456789');

    $data = buildCouponPassData();

    expect($data['webServiceURL'])->toBe('https://example.test/passkit');
});

it('falls back to config(app.url) when the host is not set and the app URL is HTTPS', function () {
    config()->set('mobile-pass.apple.webservice.host', null);
    config()->set('mobile-pass.apple.webservice.secret', 'a-very-secret-token-0123456789');
    config()->set('app.url', 'https://my-app.test');

    $data = buildCouponPassData();

    expect($data['webServiceURL'])->toBe('https://my-app.test/passkit')
        ->and($data['authenticationToken'])->toBe('a-very-secret-token-0123456789');
});

it('ignores a non-HTTPS app.url when the host is not set', function () {
    config()->set('mobile-pass.apple.webservice.host', null);
    config()->set('mobile-pass.apple.webservice.secret', 'a-very-secret-token-0123456789');
    config()->set('app.url', 'http://localhost');

    $data = buildCouponPassData();

    expect($data)->not->toHaveKeys(['webServiceURL', 'authenticationToken']);
});

it('preserves a custom path in the configured host (for users with a route prefix)', function () {
    config()->set('mobile-pass.apple.webservice.host', 'https://example.test/api');
    config()->set('mobile-pass.apple.webservice.secret', 'a-very-secret-token-0123456789');

    $data = buildCouponPassData();

    expect($data['webServiceURL'])->toBe('https://example.test/api/passkit');
});
