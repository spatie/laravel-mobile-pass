<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

class EventDateInfo implements Arrayable
{
    public function __construct(
        public ?Carbon $date = null,
        public ?bool $ignoreTimeComponents = null,
        public ?string $timeZone = null,
        public ?bool $unannounced = null,
        public ?bool $undetermined = null,
    ) {}

    public static function make(
        ?Carbon $date = null,
        ?bool $ignoreTimeComponents = null,
        ?string $timeZone = null,
        ?bool $unannounced = null,
        ?bool $undetermined = null,
    ): self {
        return new self($date, $ignoreTimeComponents, $timeZone, $unannounced, $undetermined);
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        return new self(
            isset($values['date']) ? Carbon::parse($values['date']) : null,
            $values['ignoreTimeComponents'] ?? null,
            $values['timeZone'] ?? null,
            $values['unannounced'] ?? null,
            $values['undetermined'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'date' => $this->date?->toIso8601String(),
            'ignoreTimeComponents' => $this->ignoreTimeComponents,
            'timeZone' => $this->timeZone,
            'unannounced' => $this->unannounced,
            'undetermined' => $this->undetermined,
        ], fn ($value) => $value !== null);
    }
}
