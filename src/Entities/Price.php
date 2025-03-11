<?php

namespace Spatie\LaravelMobilePass\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Price implements Arrayable
{
    public function __construct(
        public ?string $amount = null,
        public ?string $currencyCode = null,
    )
    {
    }

    public static function make(
        ?string $amount = null,
        ?string $currencyCode = null
    )
    {
        return new self(
            amount: $amount,
            currencyCode: $currencyCode,
        );
    }

    public function toArray()
    {
        return [
            'amount' => $this->amount,
            'currencyCode' => $this->currencyCode,
        ];
    }
}
