<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

it('Apple expire sets voided and expirationDate, triggering APNs push', function () {
    Http::fake();

    $pass = MobilePass::factory()->hasRegistrations(1)->create([
        'platform' => Platform::Apple,
    ]);

    $pass->expire();

    expect($pass->fresh()->content['voided'])->toBeTrue();
    expect($pass->fresh()->content['expirationDate'])->not()->toBeNull();
    Http::assertSent(fn ($request) => $request->method() === 'POST');
});

it('Google expire patches state=EXPIRED', function () {
    Http::fake(['*' => Http::response([], 200)]);
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content' => [
            'googleClassType' => 'eventTicketClass',
            'googleObjectId' => '3388.john',
            'googleObjectPayload' => ['ticketHolderName' => 'Jane'],
        ],
    ]);

    $pass->expire();

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request['state'] === 'EXPIRED'
    );
    expect($pass->fresh()->expired_at)->not()->toBeNull();
});

it('Apple addToWalletUrl returns a signed download route', function () {
    $pass = MobilePass::factory()->create(['platform' => Platform::Apple]);

    $url = $pass->addToWalletUrl();

    expect($url)->toContain('/apple/'.$pass->id.'/download');
    expect($url)->toContain('signature=');
});

it('Google addToWalletUrl returns a pay.google.com save URL', function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content' => [
            'googleClassType' => 'eventTicketClass',
            'googleObjectId' => '3388.john',
        ],
    ]);

    $url = $pass->addToWalletUrl();

    expect($url)->toStartWith('https://pay.google.com/gp/v/save/');
});
