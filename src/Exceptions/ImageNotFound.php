<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;

class ImageNotFound extends Exception implements MobilePassException
{
    public static function atPath(string $path): self
    {
        return new self("No image file found at path `{$path}`.");
    }
}
