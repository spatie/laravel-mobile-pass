<?php

namespace Spatie\LaravelMobilePass\Support\Apple;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use ZipArchive;

class PkPassReader implements Arrayable
{
    protected ZipArchive $contentZip;

    protected string $tempFile;

    public static function fromFile(string $path): self
    {
        return self::fromString(file_get_contents($path));
    }

    public static function fromString(string $content): self
    {
        return new self($content);
    }

    protected function __construct(protected string $zipString)
    {
        $this->contentZip = new ZipArchive;

        $this->tempFile = tempnam(sys_get_temp_dir(), 'zip');

        file_put_contents($this->tempFile, $zipString);

        $this->contentZip->open($this->tempFile);
    }

    /** @return array<int, string> */
    public function containingFiles(): array
    {
        $files = [];

        for ($index = 0; $index < $this->contentZip->numFiles; $index++) {
            $files[] = $this->contentZip->getNameIndex($index);
        }

        return $files;
    }

    public function containsFile(string $fileName): bool
    {
        return $this->contentZip->locateName($fileName) !== false;
    }

    public function manifestProperties(?string $key = null): mixed
    {
        return $this->getJsonProperties('manifest.json', $key);
    }

    public function manifestProperty(string $key): mixed
    {
        return $this->manifestProperties($key);
    }

    public function passProperties(?string $key = null): mixed
    {
        return $this->getJsonProperties('pass.json', $key);
    }

    public function passProperty(string $key): mixed
    {
        return $this->passProperties($key);
    }

    protected function getJsonProperties(string $fileName, ?string $key = null): mixed
    {
        $properties = json_decode($this->contentZip->getFromName($fileName), true);

        if ($key === null) {
            return $properties;
        }

        return Arr::get($properties, $key);
    }

    public function __destruct()
    {
        $this->contentZip->close();

        unlink($this->tempFile);
    }

    public function toArray(): array
    {
        return [
            'files' => $this->containingFiles(),
            'manifest' => $this->manifestProperties(),
            'pass' => $this->passProperties(),
        ];
    }
}
