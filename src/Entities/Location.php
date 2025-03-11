<?php

namespace Spatie\LaravelMobilePass\Entities;

class Location
{
    public function __construct(
        public string $latitude,
        public string $longitude
    )
    {
    }

    public static function make(
        float $latitude,
        float $longitude
    )
    {
        return new self(
            latitude: $latitude,
            longitude: $longitude,
        );
    }
}
