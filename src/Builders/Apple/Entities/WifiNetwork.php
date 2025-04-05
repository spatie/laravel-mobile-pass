<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class WifiNetwork implements Arrayable
{
    public function __construct(
        public string $ssid,
        public string $password,
    ) {}

    public static function make(
        string $ssid,
        string $password,
    ): static {
        return new self(
            ssid: $ssid,
            password: $password,
        );
    }

    public static function fromArray(array $values): static
    {
        return new self(
            ssid: $values['ssid'],
            password: $values['password'],
        );
    }

    public function toArray(): array
    {
        return [
            'ssid' => $this->ssid,
            'password' => $this->password,
        ];
    }
}
