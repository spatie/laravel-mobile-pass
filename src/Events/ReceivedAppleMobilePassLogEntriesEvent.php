<?php

namespace Spatie\LaravelMobilePass\Events;

class ReceivedAppleMobilePassLogEntriesEvent
{
    /**
     * @param  array<string>  $logEntries
     */
    public function __construct(public array $logEntries) {}
}
