<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyGoogleCallbackRequest;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.issuer_id', '3388000000000000001');

    $this->root = GoogleFixtures::ecv2RootKeypair();
    $this->intermediate = GoogleFixtures::ecv2IntermediateKeypair();
});

function fakeRootKeysSuccess(): void
{
    Http::fake([
        'pay.google.com/gp/m/issuer/keys' => Http::response(
            GoogleFixtures::rootKeysResponse(test()->root['public_base64']),
        ),
    ]);
}

function ecv2Request(array $payload): Request
{
    $request = Request::create('/google/callbacks', 'POST', content: (string) json_encode($payload));
    $request->headers->set('Content-Type', 'application/json');

    return $request;
}

function buildValidPayload(array $messageOverrides = [], ?int $intermediateExpirationMs = null): array
{
    return GoogleFixtures::buildEcv2CallbackPayload(
        rootPrivatePem: test()->root['private'],
        intermediatePrivatePem: test()->intermediate['private'],
        intermediatePublicBase64: test()->intermediate['public_base64'],
        issuerId: '3388000000000000001',
        message: array_merge([
            'eventType' => 'save',
            'objectId' => '3388.john',
        ], $messageOverrides),
        intermediateExpirationMs: $intermediateExpirationMs,
    );
}

it('accepts a correctly signed ECv2SigningOnly payload and exposes the claims', function () {
    fakeRootKeysSuccess();

    $middleware = new VerifyGoogleCallbackRequest;
    $request = ecv2Request(buildValidPayload());

    $response = $middleware->handle($request, fn (Request $request) => response()->json(
        $request->attributes->get('google_callback_claims'),
    ));

    expect($response->getStatusCode())->toBe(200);
    expect(json_decode($response->getContent(), true))->toMatchArray([
        'eventType' => 'save',
        'objectId' => '3388.john',
    ]);
});

it('rejects a request with a non-JSON body', function () {
    $request = Request::create('/google/callbacks', 'POST', content: 'not json');

    (new VerifyGoogleCallbackRequest)->handle($request, fn () => response('ok'));
})->throws(AuthenticationException::class, 'Invalid Google callback payload.');

it('rejects an unsupported protocol version', function () {
    fakeRootKeysSuccess();
    $payload = buildValidPayload();
    $payload['protocolVersion'] = 'ECv1';

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request($payload), fn () => response('ok'));
})->throws(AuthenticationException::class, 'Unsupported Google callback protocol version.');

it('rejects when no issuer id is configured', function () {
    fakeRootKeysSuccess();
    config()->set('mobile-pass.google.issuer_id', null);

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request(buildValidPayload()), fn () => response('ok'));
})->throws(AuthenticationException::class, 'No Google issuer id configured.');

it('rejects a tampered signedMessage', function () {
    fakeRootKeysSuccess();
    $payload = buildValidPayload();
    $payload['signedMessage'] = (string) json_encode(['eventType' => 'save', 'objectId' => 'tampered']);

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request($payload), fn () => response('ok'));
})->throws(AuthenticationException::class, 'Invalid Google callback signature');

it('rejects an expired intermediate signing key', function () {
    fakeRootKeysSuccess();
    $payload = buildValidPayload(intermediateExpirationMs: (int) round((microtime(true) - 60) * 1000));

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request($payload), fn () => response('ok'));
})->throws(AuthenticationException::class, 'Intermediate signing key has expired');

it('rejects when the configured issuer id does not match the signed payload', function () {
    fakeRootKeysSuccess();
    config()->set('mobile-pass.google.issuer_id', '9999999999999999999');

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request(buildValidPayload()), fn () => response('ok'));
})->throws(AuthenticationException::class, 'Message signature failed verification');

it('rejects when intermediateSigningKey is missing', function () {
    fakeRootKeysSuccess();
    $payload = buildValidPayload();
    unset($payload['intermediateSigningKey']);

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request($payload), fn () => response('ok'));
})->throws(AuthenticationException::class, 'Missing intermediateSigningKey');

it('caches the root keys after the first successful verification', function () {
    fakeRootKeysSuccess();

    expect(Cache::has('mobile-pass.google.root-keys'))->toBeFalse();

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request(buildValidPayload()), fn () => response('ok'));

    expect(Cache::has('mobile-pass.google.root-keys'))->toBeTrue();
    Http::assertSentCount(1);

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request(buildValidPayload()), fn () => response('ok'));

    Http::assertSentCount(1);
});

it('does not refetch keys when usable cached keys fail to verify the payload', function () {
    fakeRootKeysSuccess();

    $stale = GoogleFixtures::ecv2StaleRootKeypair();

    Cache::put(
        'mobile-pass.google.root-keys',
        GoogleFixtures::rootKeysResponse($stale['public_base64'])['keys'],
        3600,
    );

    try {
        (new VerifyGoogleCallbackRequest)->handle(ecv2Request(buildValidPayload()), fn () => response('ok'));
    } catch (AuthenticationException) {
        // expected — cached keys are usable but can't verify this payload
    }

    Http::assertSentCount(0);
});

it('keeps cached keys around when a forged payload arrives', function () {
    fakeRootKeysSuccess();

    Cache::put(
        'mobile-pass.google.root-keys',
        GoogleFixtures::rootKeysResponse($this->root['public_base64'])['keys'],
        3600,
    );

    $payload = buildValidPayload();
    $payload['signedMessage'] = (string) json_encode(['eventType' => 'save', 'objectId' => 'forged']);

    try {
        (new VerifyGoogleCallbackRequest)->handle(ecv2Request($payload), fn () => response('ok'));
    } catch (AuthenticationException) {
        // expected
    }

    expect(Cache::has('mobile-pass.google.root-keys'))->toBeTrue();
    Http::assertSentCount(0);
});

it('throws when Google root keys cannot be fetched on a cold cache', function () {
    Http::fake([
        'pay.google.com/gp/m/issuer/keys' => Http::response('', 503),
    ]);

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request(buildValidPayload()), fn () => response('ok'));
})->throws(AuthenticationException::class, 'Failed to fetch Google root keys');

it('skips expired root keys when picking a verifier', function () {
    Http::fake([
        'pay.google.com/gp/m/issuer/keys' => Http::response([
            'keys' => [
                [
                    'keyValue' => $this->root['public_base64'],
                    'protocolVersion' => 'ECv2SigningOnly',
                    'keyExpiration' => (string) ((int) round(microtime(true) * 1000) - 1000),
                ],
            ],
        ]),
    ]);

    (new VerifyGoogleCallbackRequest)->handle(ecv2Request(buildValidPayload()), fn () => response('ok'));
})->throws(AuthenticationException::class, 'No usable Google root keys available');
