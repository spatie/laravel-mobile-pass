<?php

namespace Spatie\LaravelMobilePass\Entities;

class WifiNetwork
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
}
