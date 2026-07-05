<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Beacon implements Arrayable
{
    public function __construct(
        public string $proximityUUID,
        public ?int $major = null,
        public ?int $minor = null,
        public ?string $relevantText = null,
    ) {}

    public static function make(
        string $proximityUUID,
        ?int $major = null,
        ?int $minor = null,
        ?string $relevantText = null,
    ): self {
        return new self($proximityUUID, $major, $minor, $relevantText);
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        return new self(
            $values['proximityUUID'],
            isset($values['major']) ? (int) $values['major'] : null,
            isset($values['minor']) ? (int) $values['minor'] : null,
            $values['relevantText'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'proximityUUID' => $this->proximityUUID,
            'major' => $this->major,
            'minor' => $this->minor,
            'relevantText' => $this->relevantText,
        ], fn ($value) => $value !== null);
    }
}
