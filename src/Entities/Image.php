<?php

namespace Spatie\LaravelMobilePass\Entities;

class Image
{
    public string $x1Path;
    public ?string $x2Path;
    public ?string $x3Path;

    public static function make(
        string $x1Path,
        ?string $x2Path = null,
        ?string $x3Path = null,
    )
    {
        return new self(
            x1Path: $x1Path,
            x2Path: $x2Path,
            x3Path: $x3Path,
        );
    }
}
