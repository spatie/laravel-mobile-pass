<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Illuminate\Contracts\Support\Arrayable;

class WifiNetwork implements Arrayable
{
    public function __construct(
        public string $ssid,
        public string $password,
    ) {}

    public static function make(string $ssid, string $password): self
    {
        return new self($ssid, $password);
    }

    /** @param  array<string, string>  $values */
    public static function fromArray(array $values): self
    {
        return new self($values['ssid'], $values['password']);
    }

    public function toArray(): array
    {
        return [
            'ssid' => $this->ssid,
            'password' => $this->password,
        ];
    }
}
