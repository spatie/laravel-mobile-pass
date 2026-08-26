<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Seat implements Arrayable
{
    public function __construct(
        public ?string $description,
        public ?string $identifier,
        public ?string $number,
        public ?string $row,
        public ?string $section,
        public ?string $type,
    ) {}

    public static function make(
        ?string $description = null,
        ?string $identifier = null,
        ?string $number = null,
        ?string $row = null,
        ?string $section = null,
        ?string $type = null,
    ): self {
        return new self(
            description: $description,
            identifier: $identifier,
            number: $number,
            row: $row,
            section: $section,
            type: $type,
        );
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        return new self(
            description: $values['seatDescription'] ?? null,
            identifier: $values['seatIdentifier'] ?? null,
            number: $values['seatNumber'] ?? null,
            row: $values['seatRow'] ?? null,
            section: $values['seatSection'] ?? null,
            type: $values['seatType'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'seatDescription' => $this->description,
            'seatIdentifier' => $this->identifier,
            'seatNumber' => $this->number,
            'seatRow' => $this->row,
            'seatSection' => $this->section,
            'seatType' => $this->type,
        ], fn ($value) => $value !== null);
    }
}
