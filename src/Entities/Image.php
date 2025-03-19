<?php

namespace Spatie\LaravelMobilePass\Entities;

class Image
{
    public function __construct(
        public string $x1Path,
        public ?string $x2Path = null,
        public ?string $x3Path = null
    ) {}

    public static function make(
        string $x1Path,
        ?string $x2Path = null,
        ?string $x3Path = null,
    ) {
        return new self(
            x1Path: $x1Path,
            x2Path: $x2Path,
            x3Path: $x3Path,
        );
    }

    public static function fromArray(array $image): self
    {
        return new self(
            x1Path: $image['x1Path'],
            x2Path: $image['x2Path'] ?? null,
            x3Path: $image['x3Path'] ?? null,
        );
    }
}
