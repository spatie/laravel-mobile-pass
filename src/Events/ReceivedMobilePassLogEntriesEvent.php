<?php

namespace Spatie\LaravelMobilePass\Events;

class ReceivedMobilePassLogEntriesEvent
{
    public function __construct(public array $logEntries) {}
}
