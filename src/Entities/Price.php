<?php

namespace Spatie\LaravelMobilePass\Entities;

class Price
{
    public function __construct(
        public ?int $amountInSmallestUnit = null,
        public ?string $currencyCode = null,
    )
    {
    }

    public static function make(
        ?int $amountInSmallestUnit = null,
        ?string $currencyCode = null
    )
    {
        return new self(
            amountInSmallestUnit: $amountInSmallestUnit,
            currencyCode: $currencyCode,
        );
    }
}
