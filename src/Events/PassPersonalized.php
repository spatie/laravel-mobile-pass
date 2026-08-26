<?php

namespace Spatie\LaravelMobilePass\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Spatie\LaravelMobilePass\Models\MobilePass;

class PassPersonalized
{
    use Dispatchable;

    /** @param  array<string, mixed>  $submittedInfo */
    public function __construct(
        public MobilePass $mobilePass,
        public array $submittedInfo,
    ) {}
}
