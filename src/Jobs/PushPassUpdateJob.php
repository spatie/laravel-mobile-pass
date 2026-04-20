<?php

namespace Spatie\LaravelMobilePass\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\LaravelMobilePass\Models\MobilePass;

class PushPassUpdateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @param class-string $actionClass */
    public function __construct(
        public MobilePass $mobilePass,
        public string $actionClass,
    ) {
        $connection = config('mobile-pass.queue.connection');

        if ($connection === null) {
            return;
        }

        $this->onConnection($connection)->onQueue(config('mobile-pass.queue.name', 'default'));
    }

    public static function dispatch(...$arguments): mixed
    {
        if (config('mobile-pass.queue.connection') === null) {
            return self::dispatchSync(...$arguments);
        }

        return static::newPendingDispatch(new static(...$arguments));
    }

    public function handle(): void
    {
        app($this->actionClass)->execute($this->mobilePass);
    }
}
