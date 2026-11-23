<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Spatie\LaravelMobilePass\Builders\Apple\CouponPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\GenericPassBuilder;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassPersonalization;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassRegistration;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Apple\AuthenticationToken;
use Spatie\LaravelMobilePass\Support\Apple\PkPassReader;

beforeEach(function () {
    config(['mobile-pass.apple.webservice.secret' => 'a-very-secret-token-0123456789']);
});

function callAppleWebService(string $method, string $routeName, MobilePass $pass, ?string $token): TestResponse
{
    if ($routeName === 'mobile-pass.apple.personalize') {
        $pass->personalization()->firstOr(
            fn () => AppleMobilePassPersonalization::factory()->create(['mobile_pass_id' => $pass->getKey()])
        );
    }

    return test()->json(
        $method,
        route($routeName, [
            'deviceId' => 'device-1',
            'passTypeId' => 'pass.com.example',
            'passSerial' => $pass->pass_serial,
        ]),
        [
            'pushToken' => 'push-token-1',
            'personalizationToken' => 'personalization-token-1',
            'requiredPersonalizationInfo' => ['fullName' => 'John Appleseed'],
        ],
        $token === null ? [] : ['Authorization' => "ApplePass {$token}"],
    );
}

function passGeneratedWithSharedSecret(): MobilePass
{
    $pass = MobilePass::factory()->create();

    $pass->updateQuietly([
        'content' => [...$pass->content, 'authenticationToken' => 'a-very-secret-token-0123456789'],
    ]);

    return $pass;
}

dataset('endpoints that require the pass token', [
    'get the latest pass' => ['GET', 'mobile-pass.check-for-updates'],
    'register a device' => ['POST', 'mobile-pass.register-device'],
    'unregister a device' => ['DELETE', 'mobile-pass.unregister-device'],
    'personalize' => ['POST', 'mobile-pass.apple.personalize'],
]);

it('rejects the shared secret', function (string $method, string $routeName) {
    $pass = MobilePass::factory()->create();

    callAppleWebService($method, $routeName, $pass, 'a-very-secret-token-0123456789')
        ->assertUnauthorized();
})->with('endpoints that require the pass token');

it('rejects a token that is derived from the shared secret alone', function () {
    $pass = MobilePass::factory()->create();

    $token = hash_hmac('sha256', $pass->pass_serial, 'a-very-secret-token-0123456789');

    callAppleWebService('GET', 'mobile-pass.check-for-updates', $pass, $token)
        ->assertUnauthorized();
});

it('accepts the shared secret for a pass that was generated with it', function (string $method, string $routeName) {
    callAppleWebService($method, $routeName, passGeneratedWithSharedSecret(), 'a-very-secret-token-0123456789')
        ->assertSuccessful();
})->with('endpoints that require the pass token');

it('rejects the shared secret for every pass when accept_shared_secret is disabled', function (string $method, string $routeName) {
    config(['mobile-pass.apple.webservice.accept_shared_secret' => false]);

    callAppleWebService($method, $routeName, passGeneratedWithSharedSecret(), 'a-very-secret-token-0123456789')
        ->assertUnauthorized();
})->with('endpoints that require the pass token');

it('rejects every request when no secret is configured', function (?string $secret) {
    config(['mobile-pass.apple.webservice.secret' => $secret]);

    $pass = MobilePass::factory()->create();

    callAppleWebService('GET', 'mobile-pass.check-for-updates', $pass, '')
        ->assertUnauthorized();

    callAppleWebService('GET', 'mobile-pass.check-for-updates', $pass, AuthenticationToken::forSerial($pass->pass_serial))
        ->assertUnauthorized();
})->with([null, '']);

it('generates passes that authenticate with their own token only', function (string $method, string $routeName) {
    config(['mobile-pass.apple.webservice.host' => 'https://example.test']);

    [$pass, $otherPass] = collect(['serial-1', 'serial-2'])->map(
        fn (string $serial) => CouponPassBuilder::make()
            ->setOrganizationName('Acme')
            ->setDescription('Coupon')
            ->setSerialNumber($serial)
            ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
            ->save()
    );

    $token = PkPassReader::fromString($pass->generate())->passProperty('authenticationToken');

    callAppleWebService($method, $routeName, $otherPass, $token)->assertUnauthorized();
    callAppleWebService($method, $routeName, $pass, $token)->assertSuccessful();
    callAppleWebService($method, $routeName, $pass, 'a-very-secret-token-0123456789')->assertUnauthorized();

    expect($token)->not->toContain('a-very-secret-token-0123456789');
})->with('endpoints that require the pass token');

it('keeps a new pass token when saving and serving an update', function () {
    config(['mobile-pass.apple.webservice.host' => 'https://example.test']);

    $pass = CouponPassBuilder::make()
        ->setDescription('Original coupon')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->save();

    $original = PkPassReader::fromString($pass->generate());
    $token = $original->passProperty('authenticationToken');

    $pass->builder()->setDescription('Updated coupon')->save();

    $response = callAppleWebService('GET', 'mobile-pass.check-for-updates', $pass, $token)->assertOk();
    $updated = PkPassReader::fromString($response->getContent());

    expect($updated->passProperty('authenticationToken'))->toBe($token)
        ->and($updated->passProperty('serialNumber'))->toBe($original->passProperty('serialNumber'))
        ->and($updated->passProperty('description'))->toBe('Updated coupon')
        ->and($pass->fresh()->content['authenticationToken'])->toBe($token);
});

it('keeps the shared secret in a pass that was generated with it', function () {
    config(['mobile-pass.apple.webservice.host' => 'https://example.test']);

    $pass = passGeneratedWithSharedSecret();

    $response = callAppleWebService('GET', 'mobile-pass.check-for-updates', $pass, 'a-very-secret-token-0123456789')
        ->assertSuccessful();

    expect(PkPassReader::fromString($response->getContent())->passProperty('authenticationToken'))
        ->toBe('a-very-secret-token-0123456789');

    expect($pass->builder()->save()->content['authenticationToken'])
        ->toBe('a-very-secret-token-0123456789');
});

it('does not hand the shared secret or the token of another pass to a new pass', function () {
    config(['mobile-pass.apple.webservice.host' => 'https://example.test']);

    $pass = passGeneratedWithSharedSecret();
    $otherPass = MobilePass::factory()->create();

    $copy = (new GenericPassBuilder($pass->content, $pass->images))->setSerialNumber('copy')->save();

    expect($copy->content['authenticationToken'])->toBe(AuthenticationToken::forSerial('copy'));

    $otherPass->updateQuietly(['content' => [...$otherPass->content, 'serialNumber' => $pass->pass_serial]]);

    expect($otherPass->builder()->data()['authenticationToken'])
        ->toBe(AuthenticationToken::forSerial($otherPass->pass_serial));
});

it('registers and unregisters using the route identifiers instead of request input', function (string $inputSource) {
    $registration = AppleMobilePassRegistration::factory()->create();
    $otherRegistration = AppleMobilePassRegistration::factory()->create(['device_id' => $registration->device_id]);
    $otherDeviceRegistration = AppleMobilePassRegistration::factory()->create(['mobile_pass_id' => $registration->mobile_pass_id]);
    $pass = $registration->pass;

    $headers = ['Authorization' => 'ApplePass '.AuthenticationToken::forSerial($pass->pass_serial)];
    $parameters = ['deviceId' => $registration->device_id, 'passTypeId' => 'pass.com.example', 'passSerial' => $pass->pass_serial];
    $input = [
        'deviceId' => $otherDeviceRegistration->device_id,
        'passTypeId' => 'pass.com.attacker',
        'passSerial' => $otherRegistration->pass->pass_serial,
    ];

    $unregisterUrl = route('mobile-pass.unregister-device', $parameters);
    $registerUrl = route('mobile-pass.register-device', $parameters);

    if ($inputSource === 'query') {
        $unregisterUrl .= '?'.http_build_query($input);
        $registerUrl .= '?'.http_build_query($input);
        $input = [];
    }

    $this->deleteJson($unregisterUrl, $input, $headers)->assertOk();

    expect($registration->fresh())->toBeNull()
        ->and($otherRegistration->fresh())->not->toBeNull()
        ->and($otherDeviceRegistration->fresh())->not->toBeNull();

    $this->postJson($registerUrl, ['pushToken' => 'push-token-1', ...$input], $headers)->assertCreated();

    $this->assertDatabaseHas('apple_mobile_pass_registrations', [
        'mobile_pass_id' => $pass->getKey(),
        'device_id' => $registration->device_id,
        'pass_type_id' => 'pass.com.example',
    ]);

    expect($pass->registrations()->count())->toBe(2)
        ->and($otherRegistration->pass->registrations()->count())->toBe(1)
        ->and($registration->device->fresh()->push_token)->toBe('push-token-1')
        ->and($otherDeviceRegistration->device->fresh()->push_token)->not->toBe('push-token-1');
})->with(['body', 'query']);

it('does not require a token to list the updated passes of a device', function () {
    $registration = AppleMobilePassRegistration::factory()->create();

    $this
        ->getJson(route('mobile-pass.get-associated-serials-for-device', [
            'deviceId' => $registration->device_id,
            'passTypeId' => $registration->pass_type_id,
        ]))
        ->assertSuccessful()
        ->assertJson(['serialNumbers' => [$registration->pass->pass_serial]]);
});

it('does not require a token to receive logs', function () {
    $this
        ->postJson(route('mobile-pass.logs'), ['logs' => ['entry']])
        ->assertSuccessful();
});

it('responds with a plain 401 when the Apple auth header is wrong or missing', function (array $headers, bool $hasLoginRoute) {
    if ($hasLoginRoute) {
        Route::get('login', fn () => 'Login')->name('login');
    }

    $pass = MobilePass::factory()->create();

    $this
        ->get(route('mobile-pass.check-for-updates', [
            'passTypeId' => 'pass.com.example',
            'passSerial' => $pass->pass_serial,
        ]), $headers)
        ->assertUnauthorized();

    $this
        ->post(route('mobile-pass.register-device', [
            'deviceId' => '12345',
            'passTypeId' => 'pass.com.example',
            'passSerial' => $pass->pass_serial,
        ]), ['pushToken' => '12345'], $headers)
        ->assertUnauthorized();
})->with([
    'wrong header' => [['Authorization' => 'ApplePass incorrect']],
    'missing header' => [[]],
])->with([
    'without a login route' => false,
    'with a login route' => true,
]);
