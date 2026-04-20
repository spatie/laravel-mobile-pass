<?php

namespace Spatie\LaravelMobilePass\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;

class GoogleMobilePassRemoved
{
    use Dispatchable;

    public Carbon $receivedAt;

    public function __construct(
        public MobilePass $mobilePass,
        public GoogleMobilePassEvent $event,
    ) {
        $this->receivedAt = $event->received_at;
    }
}
