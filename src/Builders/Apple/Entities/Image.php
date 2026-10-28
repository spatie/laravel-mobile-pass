<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Entities;

use Spatie\LaravelMobilePass\Exceptions\ImageNotFound;

class Image
{
    public function __construct(
        public string $x1Path,
        public ?string $x2Path = null,
        public ?string $x3Path = null,
        public bool $isRemote = false,
    ) {
        if ($this->isRemote) {
            foreach (array_filter([$x1Path, $x2Path, $x3Path]) as $url) {
                self::assertHttpUrl($url);
            }

            return;
        }

        self::assertFileExists($x1Path);

        if ($x2Path !== null) {
            self::assertFileExists($x2Path);
        }

        if ($x3Path !== null) {
            self::assertFileExists($x3Path);
        }
    }

    public static function make(
        string $x1Path,
        ?string $x2Path = null,
        ?string $x3Path = null,
    ): self {
        return new self($x1Path, $x2Path, $x3Path);
    }

    public static function makeRemote(
        string $x1Url,
        ?string $x2Url = null,
        ?string $x3Url = null,
    ): self {
        return new self($x1Url, $x2Url, $x3Url, isRemote: true);
    }

    /** @param  array<string, string|bool|null>  $image */
    public static function fromArray(array $image): self
    {
        return new self(
            $image['x1Path'],
            $image['x2Path'] ?? null,
            $image['x3Path'] ?? null,
            $image['isRemote'] ?? false,
        );
    }

    /** Rejects schemes like file:// or php:// that would read local files into the pass. */
    private static function assertHttpUrl(string $url): void
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ImageNotFound::unsupportedUrl($url);
        }
    }

    private static function assertFileExists(string $path): void
    {
        if (! file_exists($path)) {
            throw ImageNotFound::atPath($path);
        }
    }
}
