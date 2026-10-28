<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PassAlreadyPersonalized extends ConflictHttpException implements MobilePassException
{
    public static function forSerial(string $passSerial): self
    {
        return new self("Pass `{$passSerial}` has already been personalized.");
    }
}
