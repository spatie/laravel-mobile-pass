<?php

namespace Spatie\LaravelMobilePass\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Location implements Arrayable
{
    public function __construct(
        public string $latitude,
        public string $longitude
    ) {}

    public static function make(
        float $latitude,
        float $longitude
    ) {
        return new self(
            latitude: $latitude,
            longitude: $longitude,
        );
    }

    public function toArray()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
