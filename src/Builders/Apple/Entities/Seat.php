<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Seat implements Arrayable
{
    public function __construct(
        public ?string $aisle,
        public ?string $description,
        public ?string $identifier,
        public ?string $level,
        public ?string $number,
        public ?string $row,
        public ?string $section,
        public ?string $sectionColor,
        public ?string $type,
    ) {}

    public static function make(
        ?string $aisle = null,
        ?string $description = null,
        ?string $identifier = null,
        ?string $level = null,
        ?string $number = null,
        ?string $row = null,
        ?string $section = null,
        ?string $sectionColor = null,
        ?string $type = null,
    ): self {
        return new self(
            aisle: $aisle,
            description: $description,
            identifier: $identifier,
            level: $level,
            number: $number,
            row: $row,
            section: $section,
            sectionColor: $sectionColor,
            type: $type,
        );
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        return new self(
            aisle: $values['seatAisle'] ?? null,
            description: $values['seatDescription'] ?? null,
            identifier: $values['seatIdentifier'] ?? null,
            level: $values['seatLevel'] ?? null,
            number: $values['seatNumber'] ?? null,
            row: $values['seatRow'] ?? null,
            section: $values['seatSection'] ?? null,
            sectionColor: $values['seatSectionColor'] ?? null,
            type: $values['seatType'] ?? null,
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
