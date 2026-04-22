<?php

use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google', [
        'issuer_id' => null,
        'service_account_key' => null,
        'service_account_key_path' => null,
    ]);
});

it('throws InvalidConfig when no key is configured', function () {
    GoogleCredentials::key();
})->throws(InvalidConfig::class);

it('loads credentials from a file path', function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('loads credentials from raw JSON contents', function () {
    config()->set('mobile-pass.google.service_account_key', GoogleFixtures::serviceAccountContents());

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('loads credentials from base64-encoded contents', function () {
    config()->set(
        'mobile-pass.google.service_account_key',
        base64_encode(GoogleFixtures::serviceAccountContents())
    );

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('prefers inline contents over path when both are set', function () {
    config()->set('mobile-pass.google.service_account_key_path', '/does/not/exist.json');
    config()->set('mobile-pass.google.service_account_key', GoogleFixtures::serviceAccountContents());

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('exposes the private key as PEM', function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());

    expect(GoogleCredentials::privateKey())->toContain('-----BEGIN PRIVATE KEY-----');
});

it('returns the configured issuer id', function () {
    config()->set('mobile-pass.google.issuer_id', '3388000000012345678');

    expect(GoogleCredentials::issuerId())->toBe('3388000000012345678');
});
