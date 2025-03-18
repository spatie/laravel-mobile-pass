<?php

namespace Spatie\LaravelMobilePass\Events;

use Spatie\LaravelMobilePass\Models\MobilePass;

class MobilePassUpdatedEvent
{
    public function __construct(public MobilePass $mobilePass) {}
}
