<?php

namespace Spatie\LaravelMobilePass\Events;

use Spatie\LaravelMobilePass\Models\MobilePassRegistration;

class MobilePassRegisteredEvent
{
    public function __construct(public MobilePassRegistration $registration) {}
}
