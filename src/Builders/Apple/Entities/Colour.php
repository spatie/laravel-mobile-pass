<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Stringable;

class Colour implements Stringable
{
    public function __construct(
        public int $red,
        public int $green,
        public int $blue
    ) {}

    public static function make(
        int $red,
        int $green,
        int $blue
    ): self {
        return new self(
            red: $red,
            green: $green,
            blue: $blue
        );
    }

    public static function makeFromRgbString(?string $rgb): ?self
    {
        if (! $rgb) {
            return null;
        }

        [$red, $green, $blue] = sscanf($rgb, 'rgb(%d, %d, %d)');

        return new self(
            red: (int) $red,
            green: (int) $green,
            blue: (int) $blue
        );
    }

    public static function makeFromHex(string $hex): self
    {
        [$red, $green, $blue] = sscanf($hex, '#%02x%02x%02x');

        return new self(
            red: $red,
            green: $green,
            blue: $blue
        );
    }

    public function __toString(): string
    {
        return "rgb({$this->red}, {$this->green}, {$this->blue})";
    }
}
