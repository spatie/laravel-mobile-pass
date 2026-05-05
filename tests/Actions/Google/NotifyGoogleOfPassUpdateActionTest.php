<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('patches the Google object with the current payload', function () {
    Http::fake(['*' => Http::response([], 200)]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content' => [
            'googleClassType' => 'eventTicketClass',
            'googleObjectId' => '3388.john',
            'googleObjectPayload' => ['ticketHolderName' => 'Jane Smith'],
        ],
    ]);

    app(NotifyGoogleOfPassUpdateAction::class)->execute($pass);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && str_ends_with($request->url(), '/eventTicketObject/3388.john')
        && $request['ticketHolderName'] === 'Jane Smith'
    );
});

it('is a no-op when googleObjectId is missing', function () {
    Http::fake(['*' => Http::response([], 200)]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content' => [],
    ]);

    app(NotifyGoogleOfPassUpdateAction::class)->execute($pass);

    Http::assertNothingSent();
});
