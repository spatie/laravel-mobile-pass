<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

class Image
{
    public function __construct(
        public string $x1Path,
        public ?string $x2Path = null,
        public ?string $x3Path = null
    ) {
        if (! file_exists($x1Path)) {
            throw new \InvalidArgumentException("File not found at path: {$x1Path}");
        }

        if ($x2Path && ! file_exists($x2Path)) {
            throw new \InvalidArgumentException("File not found at path: {$x2Path}");
        }

        if ($x3Path && ! file_exists($x3Path)) {
            throw new \InvalidArgumentException("File not found at path: {$x3Path}");
        }
    }

    public static function make(
        string $x1Path,
        ?string $x2Path = null,
        ?string $x3Path = null,
    ): static {
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
