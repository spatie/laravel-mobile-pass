<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Events\MobilePassAdded;
use Spatie\LaravelMobilePass\Events\MobilePassRemoved;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.issuer_id', '3388000000000000001');

    $this->root = GoogleFixtures::ecv2RootKeypair();
    $this->intermediate = GoogleFixtures::ecv2IntermediateKeypair();

    Http::fake([
        'pay.google.com/gp/m/issuer/keys' => Http::response(
            GoogleFixtures::rootKeysResponse($this->root['public_base64']),
        ),
    ]);
});

function ecv2CallbackPayload(array $message): array
{
    return GoogleFixtures::buildEcv2CallbackPayload(
        rootPrivatePem: test()->root['private'],
        intermediatePrivatePem: test()->intermediate['private'],
        intermediatePublicBase64: test()->intermediate['public_base64'],
        issuerId: '3388000000000000001',
        message: $message,
    );
}

it('records a save event and fires the Laravel event', function () {
    Event::fake([MobilePassAdded::class]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content' => ['googleObjectId' => '3388.john'],
    ]);

    $this->postJson(
        route('mobile-pass.google.callback'),
        ecv2CallbackPayload([
            'eventType' => 'save',
            'objectId' => '3388.john',
        ]),
    )->assertNoContent();

    expect(GoogleMobilePassEvent::where('mobile_pass_id', $pass->id)->saves()->count())->toBe(1);

    Event::assertDispatched(
        fn (MobilePassAdded $event) => $event->mobilePass->is($pass),
    );
});

it('records a remove event and fires the Laravel event', function () {
    Event::fake([MobilePassRemoved::class]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content' => ['googleObjectId' => '3388.john'],
    ]);

    $this->postJson(
        route('mobile-pass.google.callback'),
        ecv2CallbackPayload([
            'eventType' => 'del',
            'objectId' => '3388.john',
        ]),
    )->assertNoContent();

    expect(GoogleMobilePassEvent::where('mobile_pass_id', $pass->id)->removes()->count())->toBe(1);

    Event::assertDispatched(
        fn (MobilePassRemoved $event) => $event->mobilePass->is($pass),
    );
});
