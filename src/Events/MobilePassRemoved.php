<?php

namespace Spatie\LaravelMobilePass\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Spatie\LaravelMobilePass\Models\MobilePass;

class MobilePassRemoved
{
    use Dispatchable;

    public function __construct(public MobilePass $mobilePass) {}
}
