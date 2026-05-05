<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Location implements Arrayable
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?float $altitude = null,
        public ?string $relevantText = null,
    ) {}

    public static function make(
        float $latitude,
        float $longitude,
        ?float $altitude = null,
        ?string $relevantText = null,
    ): self {
        return new self($latitude, $longitude, $altitude, $relevantText);
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        return new self(
            (float) $values['latitude'],
            (float) $values['longitude'],
            isset($values['altitude']) ? (float) $values['altitude'] : null,
            $values['relevantText'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'altitude' => $this->altitude,
            'relevantText' => $this->relevantText,
        ], fn ($value) => $value !== null);
    }
}
