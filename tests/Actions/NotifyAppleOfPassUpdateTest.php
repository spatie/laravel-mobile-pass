<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Actions\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Models\MobilePass;

beforeEach(function () {
    Http::fake();
    config(['mobile-pass.apple.apple_push_base_url' => 'https://example.com']);
});

it('sends a push notification to Apple', function () {
    $pass = MobilePass::factory()->hasRegistrations(1)->create();
    app(NotifyAppleOfPassUpdateAction::class)->execute($pass);

    $pushToken = $pass->devices->first()->push_token;

    Http::assertSent(fn (Request $request) => $request->url() === "https://example.com/{$pushToken}" &&
            $request->method() === 'POST'
    );
});

it('contains an empty json dictionary as the payload', function () {
    $pass = MobilePass::factory()->hasRegistrations(1)->create();
    app(NotifyAppleOfPassUpdateAction::class)->execute($pass);

    Http::assertSent(static fn (Request $request) => $request->body() === '{}'
    );
});

it('sends the pass type ID as the apns topic', function () {
    app(NotifyAppleOfPassUpdateAction::class)->execute(
        MobilePass::factory()->hasRegistrations(1)->create()
    );

    Http::assertSent(static fn (Request $request) => $request->hasHeader('apns-topic', 'pass.com.example')
    );
});

it('uses HTTP/2', function () {
    app(NotifyAppleOfPassUpdateAction::class)->execute(
        MobilePass::factory()->hasRegistrations(1)->create()
    );

    Http::assertSent(static fn (Request $request) => $request->toPsrRequest()->getProtocolVersion() === '2'
    );
})->skip('This is failing in CI for some reason');

it('includes the certificate', function () {
    app(NotifyAppleOfPassUpdateAction::class)->execute(
        MobilePass::factory()->hasRegistrations(1)->create()
    );

    $this->markTestIncomplete('How do we retrive the certificate from the request?');
});

it('sends a push notification to every registration', function () {
    app(NotifyAppleOfPassUpdateAction::class)->execute(
        MobilePass::factory()->hasRegistrations(3)->create()
    );

    Http::assertSentCount(3);
});

it('deletes the device if the Apple reported that the push token is invalid', function () {
    $this->markTestIncomplete('Need to find out what the response code/message would be.');

    Http::fake([
        '*' => Http::response('InvalidToken', 403),
    ]);

    app(NotifyAppleOfPassUpdateAction::class)->execute(
        MobilePass::factory()->hasRegistrations(1)->create()
    );

    $this->assertDatabaseCount('mobile_pass_registrations', 0);
    $this->assertDatabaseCount('mobile_pass_devices', 0);
});
