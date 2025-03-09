<?php

namespace Spatie\LaravelMobilePass\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\LaravelMobilePass\LaravelMobilePass
 */
class LaravelMobilePass extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Spatie\LaravelMobilePass\LaravelMobilePass::class;
    }
}
