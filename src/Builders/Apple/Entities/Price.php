<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Price implements Arrayable
{
    public function __construct(
        public ?string $amount = null,
        public ?string $currencyCode = null,
    ) {}

    public static function make(
        ?string $amount = null,
        ?string $currencyCode = null
    ): static {
        return new self(
            amount: $amount,
            currencyCode: $currencyCode,
        );
    }

    public static function fromArray(array $values): static
    {
        return new self(
            amount: $values['amount'] ?? null,
            currencyCode: $values['currencyCode'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currencyCode' => $this->currencyCode,
        ];
    }
}
