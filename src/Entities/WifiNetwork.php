<?php

namespace Spatie\LaravelMobilePass\Entities;

use Illuminate\Contracts\Support\Arrayable;

class WifiNetwork implements Arrayable
{
    public function __construct(
        public string $ssid,
        public string $password,
    )
    {
    }

    public static function make(
        string $ssid,
        string $password,
    )
    {
        return new self(
            ssid: $ssid,
            password: $password,
        );
    }

    public function toArray()
    {
        return [
            'ssid' => $this->ssid,
            'password' => $this->password,
        ];
    }
}
