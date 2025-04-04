<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;
use Spatie\LaravelMobilePass\Enums\Platform;

class PlatformDoesntSupport extends Exception
{
    public static function cannotDownload(Platform $platform)
    {
        return new static("Platform {$platform->value} doesn't support downloading passes.");
    }
}
