<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Entities;

use Illuminate\Contracts\Support\Arrayable;
use RuntimeException;

class Image implements Arrayable
{
    protected function __construct(
        public readonly ?string $url = null,
        public readonly ?string $localPath = null,
    ) {}

    public static function fromUrl(string $url): self
    {
        return new self(url: $url);
    }

    public static function fromLocalPath(string $path): self
    {
        return new self(localPath: $path);
    }

    public function publicUrl(): string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        throw new RuntimeException(
            'Image::publicUrl() on a local-path image requires the hosted image route. '
            .'Local-path images are only available on object-level builders; '
            .'use Image::fromUrl() for class-level images.'
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['sourceUri' => ['uri' => $this->publicUrl()]];
    }
}
