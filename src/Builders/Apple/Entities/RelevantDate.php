<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;

class RelevantDate implements Arrayable
{
    public function __construct(
        public ?Carbon $date = null,
        public ?Carbon $startDate = null,
        public ?Carbon $endDate = null,
    ) {}

    public static function make(
        ?Carbon $date = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
    ): self {
        return new self($date, $startDate, $endDate);
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        return new self(
            isset($values['date']) ? Carbon::parse($values['date']) : null,
            isset($values['startDate']) ? Carbon::parse($values['startDate']) : null,
            isset($values['endDate']) ? Carbon::parse($values['endDate']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'date' => $this->date?->toIso8601String(),
            'startDate' => $this->startDate?->toIso8601String(),
            'endDate' => $this->endDate?->toIso8601String(),
        ], fn ($value) => $value !== null);
    }
}
