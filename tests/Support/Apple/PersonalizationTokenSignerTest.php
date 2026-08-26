<?php

use Spatie\LaravelMobilePass\Support\Apple\PersonalizationTokenSigner;

it('produces a non-empty binary signature over the token', function () {
    $signature = (new PersonalizationTokenSigner)->sign('324389RFHF32JOID2902F3JF23092FEJI02');

    expect($signature)->not->toBeEmpty();
    expect($signature)->not->toBe('324389RFHF32JOID2902F3JF23092FEJI02');
});

it('produces different signatures for different tokens', function () {
    $signer = new PersonalizationTokenSigner;

    expect($signer->sign('token-a'))->not->toBe($signer->sign('token-b'));
});
