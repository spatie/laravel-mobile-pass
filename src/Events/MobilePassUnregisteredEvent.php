<?php

namespace Spatie\LaravelMobilePass\Events;

use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

class MobilePassUnregisteredEvent
{
    public function __construct(public MobilePassRegistration $registration) {}
}
