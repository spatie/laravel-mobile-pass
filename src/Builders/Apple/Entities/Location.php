<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Location implements Arrayable
{
    public function __construct(
        public float $latitude,
        public float $longitude
    ) {}

    public static function make(
        float $latitude,
        float $longitude
    ): self {
        return new self(
            latitude: $latitude,
            longitude: $longitude,
        );
    }

    public static function fromArray(array $values): self
    {
        return new self(
            latitude: (float) $values['latitude'],
            longitude: (float) $values['longitude'],
        );
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
