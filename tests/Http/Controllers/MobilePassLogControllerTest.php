<?php

use Illuminate\Support\Facades\Event;
use Spatie\LaravelMobilePass\Events\ReceivedAppleMobilePassLogEntriesEvent;

it('will fire an event when logs are received', function () {
    Event::fake();

    $logEntries = [
        'entry1',
        'entry2',
    ];

    $this
        ->withoutMiddleware()
        ->postJson(route('mobile-pass.logs'), ['logs' => $logEntries])
        ->assertSuccessful();

    Event::assertDispatched(function (ReceivedAppleMobilePassLogEntriesEvent $event) use ($logEntries) {
        expect($event->logEntries)->toBe($logEntries);

        return true;
    });
});
