<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Link implements Arrayable
{
    public function __construct(
        public readonly string $uri,
        public readonly ?string $description = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $payload = ['uri' => $this->uri];

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        return $payload;
    }
}
