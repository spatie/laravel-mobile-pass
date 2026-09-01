<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Seat implements Arrayable
{
    public function __construct(
        public ?string $description = null,
        public ?string $identifier = null,
        public ?string $number = null,
        public ?string $row = null,
        public ?string $section = null,
        public ?string $type = null,
        public ?string $aisle = null,
        public ?string $level = null,
        public ?string $sectionColor = null,
    ) {}

    public static function make(
        ?string $description = null,
        ?string $identifier = null,
        ?string $number = null,
        ?string $row = null,
        ?string $section = null,
        ?string $type = null,
        ?string $aisle = null,
        ?string $level = null,
        ?string $sectionColor = null,
    ): self {
        return new self(
            description: $description,
            identifier: $identifier,
            number: $number,
            row: $row,
            section: $section,
            type: $type,
            aisle: $aisle,
            level: $level,
            sectionColor: $sectionColor,
        );
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        return new self(
            description: $values['seatDescription'] ?? $values['description'] ?? null,
            identifier: $values['seatIdentifier'] ?? $values['identifier'] ?? null,
            number: $values['seatNumber'] ?? $values['number'] ?? null,
            row: $values['seatRow'] ?? $values['row'] ?? null,
            section: $values['seatSection'] ?? $values['section'] ?? null,
            type: $values['seatType'] ?? $values['type'] ?? null,
            aisle: $values['seatAisle'] ?? null,
            level: $values['seatLevel'] ?? null,
            sectionColor: $values['seatSectionColor'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'seatAisle' => $this->aisle,
            'seatDescription' => $this->description,
            'seatIdentifier' => $this->identifier,
            'seatLevel' => $this->level,
            'seatNumber' => $this->number,
            'seatRow' => $this->row,
            'seatSection' => $this->section,
            'seatSectionColor' => $this->sectionColor,
            'seatType' => $this->type,
        ], fn ($value) => $value !== null);
    }
}
