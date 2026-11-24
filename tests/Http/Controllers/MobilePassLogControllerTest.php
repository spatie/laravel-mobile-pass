<?php

use Illuminate\Support\Facades\Event;
use Spatie\LaravelMobilePass\Events\AppleMobilePassLogsReceived;

it('will fire an event when logs are received', function () {
    Event::fake();

    $logEntries = [
        'entry1',
        'entry2',
    ];

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.logs'), ['logs' => $logEntries])
        ->assertOk()
        ->assertContent('');

    Event::assertDispatched(function (AppleMobilePassLogsReceived $event) use ($logEntries) {
        expect($event->logEntries)->toBe($logEntries);

        return true;
    });
});

it('does not normalize the received log entries', function () {
    Event::fake();

    $logEntries = [
        'error' => 'entry1',
        0 => 'entry2',
    ];

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.logs'), ['logs' => $logEntries])
        ->assertOk()
        ->assertContent('');

    Event::assertDispatched(function (AppleMobilePassLogsReceived $event) use ($logEntries) {
        expect($event->logEntries)->toBe($logEntries);

        return true;
    });
});

it('returns 200 and dispatches an empty event when the logs key is missing', function () {
    Event::fake();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.logs'), [])
        ->assertOk()
        ->assertContent('');

    Event::assertDispatched(fn (AppleMobilePassLogsReceived $event) => $event->logEntries === []);
});

it('returns 200 and dispatches an empty event for an empty request body', function () {
    Event::fake();

    $this
        ->withoutMiddleware()
        ->post(route('mobile-pass.logs'))
        ->assertOk()
        ->assertContent('');

    Event::assertDispatched(fn (AppleMobilePassLogsReceived $event) => $event->logEntries === []);
});

it('does not crash when logs is a string', function () {
    Event::fake();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.logs'), ['logs' => 'entry1'])
        ->assertOk()
        ->assertContent('');

    Event::assertDispatched(fn (AppleMobilePassLogsReceived $event) => $event->logEntries === []);
});

it('does not crash when logs is null', function () {
    Event::fake();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.logs'), ['logs' => null])
        ->assertOk()
        ->assertContent('');

    Event::assertDispatched(fn (AppleMobilePassLogsReceived $event) => $event->logEntries === []);
});

it('returns 200 for a malformed json body', function () {
    Event::fake();

    $this
        ->withoutMiddleware()
        ->call('POST', route('mobile-pass.logs'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], 'not-json')
        ->assertOk()
        ->assertContent('');

    Event::assertDispatched(fn (AppleMobilePassLogsReceived $event) => $event->logEntries === []);
});

it('dispatches an event for every request', function () {
    Event::fake();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.logs'), ['logs' => ['entry1']])
        ->assertOk();

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.logs'), [])
        ->assertOk();

    Event::assertDispatchedTimes(AppleMobilePassLogsReceived::class, 2);
});
