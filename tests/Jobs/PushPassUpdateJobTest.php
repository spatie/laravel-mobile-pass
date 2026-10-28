<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Jobs\PushPassUpdateJob;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('runs sync when no queue connection is configured', function () {
    config()->set('mobile-pass.queue.connection', null);
    Bus::fake();

    $pass = MobilePass::factory()->create(['platform' => Platform::Apple]);

    PushPassUpdateJob::dispatch($pass, NotifyAppleOfPassUpdateAction::class);

    Bus::assertDispatchedSync(PushPassUpdateJob::class);
});

it('queues when a queue connection is configured', function () {
    config()->set('mobile-pass.queue.connection', 'redis');
    config()->set('mobile-pass.queue.name', 'mobile-pass');
    Queue::fake();

    $pass = MobilePass::factory()->create(['platform' => Platform::Google]);

    PushPassUpdateJob::dispatch($pass, NotifyGoogleOfPassUpdateAction::class);

    Queue::assertPushedOn('mobile-pass', PushPassUpdateJob::class);
});

it('waits for the surrounding transaction to commit before pushing', function () {
    config()->set('mobile-pass.queue.connection', null);
    config(['mobile-pass.apple.apple_push_base_url' => 'https://example.com']);
    Http::fake();

    $pass = MobilePass::factory()->hasRegistrations(1)->create(['platform' => Platform::Apple]);

    DB::transaction(function () use ($pass) {
        $pass->update(['download_name' => 'changed']);

        Http::assertNothingSent();
    });

    Http::assertSentCount(1);
});

it('does not push when the surrounding transaction rolls back', function () {
    config()->set('mobile-pass.queue.connection', null);
    config(['mobile-pass.apple.apple_push_base_url' => 'https://example.com']);
    Http::fake();

    $pass = MobilePass::factory()->hasRegistrations(1)->create(['platform' => Platform::Apple]);

    try {
        DB::transaction(function () use ($pass) {
            $pass->update(['download_name' => 'changed']);

            throw new RuntimeException('rollback');
        });
    } catch (RuntimeException) {
    }

    Http::assertNothingSent();
});
