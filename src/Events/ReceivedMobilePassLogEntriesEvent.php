<?php

namespace Spatie\LaravelMobilePass\Events;

class ReceivedMobilePassLogEntriesEvent
{
    /**
     * @param array<string> $logEntries
     */
    public function __construct(public array $logEntries)
    {
    }
}
