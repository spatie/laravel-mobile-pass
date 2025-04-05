<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

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
    ): static {
        return new self(
            latitude: $latitude,
            longitude: $longitude,
        );
    }

    public static function fromArray(array $values)
    {
        return new self(
            latitude: $values['latitude'],
            longitude: $values['longitude'],
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
