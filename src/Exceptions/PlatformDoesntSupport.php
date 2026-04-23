<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;
use Spatie\LaravelMobilePass\Enums\Platform;

class PlatformDoesntSupport extends Exception
{
    public static function cannotUpdateFields(Platform $platform): self
    {
        return new self("Platform {$platform->value} doesn't support updating fields by key. Use the platform-specific builder instead.");
    }
}
