<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Entities;

use Illuminate\Contracts\Support\Arrayable;

class TextModule implements Arrayable
{
    public function __construct(
        public readonly string $header,
        public readonly string $body,
        public readonly ?string $id = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $payload = [
            'header' => $this->header,
            'body' => $this->body,
        ];

        if ($this->id !== null) {
            $payload['id'] = $this->id;
        }

        return $payload;
    }
}
