<?php

use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google', [
        'issuer_id' => null,
        'service_account_key_base64' => null,
        'service_account_key_contents' => null,
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

it('loads credentials from raw contents', function () {
    config()->set('mobile-pass.google.service_account_key_contents', GoogleFixtures::serviceAccountContents());

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('loads credentials from base64 contents', function () {
    config()->set(
        'mobile-pass.google.service_account_key_base64',
        base64_encode(GoogleFixtures::serviceAccountContents())
    );

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('prefers base64 over contents and path when multiple are set', function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.service_account_key_contents', '{"client_email":"wrong@example.com"}');
    config()->set(
        'mobile-pass.google.service_account_key_base64',
        base64_encode(GoogleFixtures::serviceAccountContents())
    );

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
