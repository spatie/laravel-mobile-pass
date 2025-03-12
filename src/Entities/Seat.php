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
    ) {
        return new self(
            description: $description,
            identifier: $identifier,
            number: $number,
            row: $row,
            section: $section,
            type: $type,
        );
    }

    public function toArray()
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
