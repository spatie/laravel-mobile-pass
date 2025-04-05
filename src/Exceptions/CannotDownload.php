<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;
use Spatie\LaravelMobilePass\Models\MobilePass;

class CannotDownload extends Exception
{
    public static function wrongPlatform(MobilePass $mobilePass): self
    {
        return new self('Only Apple passes can be downloaded');
    }
}
