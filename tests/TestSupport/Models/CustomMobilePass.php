<?php

namespace Spatie\LaravelMobilePass\Tests\TestSupport\Models;

use Spatie\LaravelMobilePass\Models\MobilePass;

class CustomMobilePass extends MobilePass
{
    protected $table = 'custom_mobile_passes';
}
