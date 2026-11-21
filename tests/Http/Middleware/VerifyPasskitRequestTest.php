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

it('accepts the token of the requested pass', function (string $method, string $routeName) {
    $pass = MobilePass::factory()->create();

    callAppleWebService($method, $routeName, $pass, AuthenticationToken::forSerial($pass->pass_serial))
        ->assertSuccessful();
})->with('endpoints that require the pass token');

it('rejects the token of another pass', function (string $method, string $routeName) {
    $pass = MobilePass::factory()->create();
    $otherPass = MobilePass::factory()->create();

    callAppleWebService($method, $routeName, $pass, AuthenticationToken::forSerial($otherPass->pass_serial))
        ->assertUnauthorized();
})->with('endpoints that require the pass token');

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

it('generates passes that authenticate with their own token only', function () {
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

    expect($token)->not->toContain('a-very-secret-token-0123456789');

    callAppleWebService('GET', 'mobile-pass.check-for-updates', $pass, $token)->assertSuccessful();
    callAppleWebService('GET', 'mobile-pass.check-for-updates', $otherPass, $token)->assertUnauthorized();
    callAppleWebService('GET', 'mobile-pass.check-for-updates', $pass, 'a-very-secret-token-0123456789')->assertUnauthorized();
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

it('registers and unregisters a device for the pass in the URL only', function () {
    $registration = AppleMobilePassRegistration::factory()->create();
    $otherRegistration = AppleMobilePassRegistration::factory()->create(['device_id' => $registration->device_id]);
    $pass = $registration->pass;

    $headers = ['Authorization' => 'ApplePass '.AuthenticationToken::forSerial($pass->pass_serial)];
    $parameters = ['deviceId' => $registration->device_id, 'passTypeId' => 'pass.com.example', 'passSerial' => $pass->pass_serial];
    $input = ['pushToken' => 'push-token-1', 'passSerial' => $otherRegistration->pass->pass_serial];

    $this->deleteJson(route('mobile-pass.unregister-device', $parameters), $input, $headers)->assertOk();

    expect($registration->fresh())->toBeNull()
        ->and($otherRegistration->fresh())->not->toBeNull();

    $this->postJson(route('mobile-pass.register-device', $parameters), $input, $headers)->assertCreated();

    expect($pass->registrations()->count())->toBe(1)
        ->and($otherRegistration->pass->registrations()->count())->toBe(1);
});

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
