<?php

namespace Spatie\LaravelMobilePass\Entities;

use Stringable;

class Colour implements Stringable
{
    public function __construct(
        public int $red,
        public int $green,
        public int $blue
    )
    {
    }

    public static function make(
        int $red,
        int $green,
        int $blue
    )
    {
        return new static(
            red: $red,
            green: $green,
            blue: $blue
        );
    }

    public static function makeFromHex(string $hex)
    {
        // TODO: implement this
    }

    public function __toString()
    {
        return "rgb({$this->red}, {$this->green}, {$this->blue})";
    }
}