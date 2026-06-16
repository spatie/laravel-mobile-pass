<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Entities;

use Illuminate\Contracts\Support\Arrayable;

class ImageModule implements Arrayable
{
    public function __construct(
        public readonly Image $image,
        public readonly ?string $id = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $payload = ['mainImage' => $this->image->toArray()];

        if ($this->id !== null) {
            $payload['id'] = $this->id;
        }

        return $payload;
    }
}
