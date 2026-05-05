<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Support\Google\GoogleJwtSigner;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388000000012345678');
    cache()->clear();
});

it('signs a Save-to-Wallet JWT with the required claims', function () {
    $jwt = app(GoogleJwtSigner::class)->signSaveUrlJwt([
        'eventTicketObjects' => [['id' => '3388000000012345678.abc']],
    ]);

    $decoded = JWT::decode($jwt, new Key(GoogleFixtures::publicKey(), 'RS256'));

    expect($decoded->iss)->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
    expect($decoded->aud)->toBe('google');
    expect($decoded->typ)->toBe('savetowallet');
    expect($decoded->payload->eventTicketObjects[0]->id)->toBe('3388000000012345678.abc');
});

it('exchanges an assertion JWT for an access token and caches it', function () {
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'ya29.fake-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 200),
    ]);

    $token = app(GoogleJwtSigner::class)->accessToken();

    expect($token)->toBe('ya29.fake-token');

    app(GoogleJwtSigner::class)->accessToken();
    Http::assertSentCount(1);
});
