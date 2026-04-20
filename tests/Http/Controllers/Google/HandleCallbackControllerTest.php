<?php

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassRemoved;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassSaved;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.callback_signing_key', GoogleFixtures::publicKey());
});

it('records a save event and fires the Laravel event', function () {
    Event::fake([GoogleMobilePassSaved::class]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content' => ['googleObjectId' => '3388.john'],
    ]);

    $jwt = JWT::encode(
        [
            'iss' => 'google',
            'iat' => time(),
            'eventType' => 'save',
            'objectId' => '3388.john',
        ],
        GoogleFixtures::privateKey(),
        'RS256'
    );

    $this->postJson(route('mobile-pass.google.callback'), [], ['Authorization' => 'Bearer '.$jwt])
        ->assertNoContent();

    expect(GoogleMobilePassEvent::where('mobile_pass_id', $pass->id)->saves()->count())->toBe(1);

    Event::assertDispatched(GoogleMobilePassSaved::class);
});

it('records a remove event and fires the Laravel event', function () {
    Event::fake([GoogleMobilePassRemoved::class]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content' => ['googleObjectId' => '3388.john'],
    ]);

    $jwt = JWT::encode(
        ['iss' => 'google', 'iat' => time(), 'eventType' => 'del', 'objectId' => '3388.john'],
        GoogleFixtures::privateKey(),
        'RS256'
    );

    $this->postJson(route('mobile-pass.google.callback'), [], ['Authorization' => 'Bearer '.$jwt])
        ->assertNoContent();

    expect(GoogleMobilePassEvent::where('mobile_pass_id', $pass->id)->removes()->count())->toBe(1);

    Event::assertDispatched(GoogleMobilePassRemoved::class);
});
