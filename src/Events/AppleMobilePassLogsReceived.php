<?php

namespace Spatie\LaravelMobilePass\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AppleMobilePassLogsReceived
{
    use Dispatchable;

    /**
     * @param  array<string>  $logEntries
     */
    public function __construct(public array $logEntries) {}
}
