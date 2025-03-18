<?php

namespace Spatie\LaravelMobilePass\Events;

use Spatie\LaravelMobilePass\Models\MobilePass;

class MobilePassUpdated
{
    public function __construct(public MobilePass $mobilePass) {}
}
