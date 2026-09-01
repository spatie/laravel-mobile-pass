<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;

class RelevantDate implements Arrayable
{
    protected function __construct(
        public ?Carbon $date = null,
        public ?Carbon $startDate = null,
        public ?Carbon $endDate = null,
    ) {}

    public static function forDate(Carbon $date): self
    {
        return new self(date: $date);
    }

    public static function forInterval(Carbon $startDate, Carbon $endDate): self
    {
        return new self(startDate: $startDate, endDate: $endDate);
    }

    /** @param  array<string, mixed>  $values */
    public static function fromArray(array $values): self
    {
        $date = $values['date'] ?? $values['relevantDate'] ?? null;

        if ($date !== null) {
            return self::forDate(Carbon::parse($date));
        }

        if (! isset($values['startDate'], $values['endDate'])) {
            throw InvalidConfig::incompleteRelevantDate();
        }

        return self::forInterval(
            Carbon::parse($values['startDate']),
            Carbon::parse($values['endDate']),
        );
    }

    public function moment(): ?Carbon
    {
        return $this->date ?? $this->startDate;
    }

    /**
     * Wallet reads the single moment from `relevantDate` on iOS 18, and from `date`
     * since iOS 26. Writing both keeps one entry relevant on either version.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        if ($this->date !== null) {
            $date = $this->date->toIso8601String();

            return [
                'relevantDate' => $date,
                'date' => $date,
            ];
        }

        return array_filter([
            'startDate' => $this->startDate?->toIso8601String(),
            'endDate' => $this->endDate?->toIso8601String(),
        ], fn ($value) => $value !== null);
    }
}
