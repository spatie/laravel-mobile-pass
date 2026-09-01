<?php

namespace Spatie\LaravelMobilePass\Tests\Http;

use Illuminate\Support\Facades\Event;
use Spatie\LaravelMobilePass\Events\PassPersonalized;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassPersonalization;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('signs the submitted token and persists the submission', function () {
    $pass = MobilePass::factory()->create();

    AppleMobilePassPersonalization::factory()->create([
        'mobile_pass_id' => $pass->getKey(),
    ]);

    $response = $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.apple.personalize', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]), [
            'personalizationToken' => '324389RFHF32JOID2902F3JF23092FEJI02',
            'requiredPersonalizationInfo' => [
                'fullName' => 'John Appleseed',
                'emailAddress' => 'john.appleseed@icloud.com',
            ],
        ]);

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/octet-stream');
    expect($response->getContent())->not->toBeEmpty();

    $this->assertDatabaseHas('apple_mobile_pass_personalizations', [
        'mobile_pass_id' => $pass->getKey(),
        'personalization_token' => '324389RFHF32JOID2902F3JF23092FEJI02',
    ]);

    $personalization = AppleMobilePassPersonalization::where('mobile_pass_id', $pass->getKey())->first();
    expect($personalization->submitted_info)->toBe([
        'fullName' => 'John Appleseed',
        'emailAddress' => 'john.appleseed@icloud.com',
    ]);
    expect($personalization->personalized_at)->not->toBeNull();
});

it('touches the pass so check-for-updates serves a fresh pass afterward', function () {
    $pass = MobilePass::factory()->create();
    $originalUpdatedAt = $pass->updated_at;

    AppleMobilePassPersonalization::factory()->create(['mobile_pass_id' => $pass->getKey()]);

    $this->travel(1)->minutes();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.apple.personalize', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]), [
            'personalizationToken' => 'some-token',
            'requiredPersonalizationInfo' => ['fullName' => 'John Appleseed'],
        ]);

    expect($pass->fresh()->updated_at->gt($originalUpdatedAt))->toBeTrue();
});

it('fires PassPersonalized', function () {
    Event::fake([PassPersonalized::class]);

    $pass = MobilePass::factory()->create();
    AppleMobilePassPersonalization::factory()->create(['mobile_pass_id' => $pass->getKey()]);

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.apple.personalize', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]), [
            'personalizationToken' => 'some-token',
            'requiredPersonalizationInfo' => ['fullName' => 'John Appleseed'],
        ]);

    Event::assertDispatched(
        fn (PassPersonalized $event) => $event->mobilePass->is($pass)
            && $event->submittedInfo === ['fullName' => 'John Appleseed'],
    );
});

it('returns 404 if the pass doesnt exist', function () {
    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.apple.personalize', [
            'passSerial' => 'does-not-exist',
            'passTypeId' => 'pass.com.example',
        ]), [
            'personalizationToken' => 'some-token',
            'requiredPersonalizationInfo' => ['fullName' => 'John Appleseed'],
        ])
        ->assertNotFound();
});

it('returns 422 when the payload is missing required keys', function () {
    $pass = MobilePass::factory()->create();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.apple.personalize', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]), [])
        ->assertUnprocessable();
});

it('returns 404 when the pass has no personalization config', function () {
    $pass = MobilePass::factory()->create();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.apple.personalize', [
            'passSerial' => $pass->pass_serial,
            'passTypeId' => 'pass.com.example',
        ]), [
            'personalizationToken' => 'some-token',
            'requiredPersonalizationInfo' => ['fullName' => 'John Appleseed'],
        ])
        ->assertNotFound();

    $this->assertDatabaseMissing('apple_mobile_pass_personalizations', [
        'mobile_pass_id' => $pass->getKey(),
    ]);
});
