<?php

namespace Spatie\LaravelMobilePass\Entities;

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
    ): static {
        return new self(
            description: $description,
            identifier: $identifier,
            number: $number,
            row: $row,
            section: $section,
            type: $type,
        );
    }

    public static function fromArray(array $values): static
    {
        return new self(
            description: $values['description'] ?? null,
            identifier: $values['identifier'] ?? null,
            number: $values['number'] ?? null,
            row: $values['row'] ?? null,
            section: $values['section'] ?? null,
            type: $values['type'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'description' => $this->description,
            'identifier' => $this->identifier,
            'number' => $this->number,
            'row' => $this->row,
            'section' => $this->section,
            'type' => $this->type,
        ]);
    }
}
