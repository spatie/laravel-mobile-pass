<?php

use Spatie\LaravelMobilePass\Exceptions\InvalidCertificate;
use Spatie\LaravelMobilePass\Support\Apple\PersonalizationTokenSigner;

it('throws InvalidCertificate and cleans up temp files when the certificate cannot be loaded', function () {
    config()->set('mobile-pass.apple.certificate_password', 'wrong-password');

    $before = glob(sys_get_temp_dir().'/personalization-*');

    try {
        (new PersonalizationTokenSigner)->sign('some-token');

        $this->fail('Expected InvalidCertificate to be thrown.');
    } catch (InvalidCertificate $exception) {
        // expected
    }

    $after = glob(sys_get_temp_dir().'/personalization-*');

    expect($after)->toBe($before);
});

it('produces a non-empty binary signature over the token', function () {
    $signature = (new PersonalizationTokenSigner)->sign('324389RFHF32JOID2902F3JF23092FEJI02');

    expect($signature)->not->toBeEmpty();
    expect($signature)->not->toBe('324389RFHF32JOID2902F3JF23092FEJI02');
});

it('produces different signatures for different tokens', function () {
    $signer = new PersonalizationTokenSigner;

    expect($signer->sign('token-a'))->not->toBe($signer->sign('token-b'));
});
