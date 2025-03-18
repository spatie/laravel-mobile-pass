<?php

namespace Spatie\LaravelMobilePass\Support;

use Illuminate\Support\Arr;
use ZipArchive;

class PkPassReader
{
    protected ZipArchive $contentZip;

    protected string $tempFile;

    public static function loadFromFile(string $path): self
    {
        $content = file_get_contents($path);

        return self::loadFromString($content);
    }

    public static function loadFromString(string $content): self
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

    public function containingFiles(): array
    {
        if ($this->contentZip->numFiles === 0) {
            return [];
        }

        $files = [];

        foreach (range(0, $this->contentZip->numFiles - 1) as $i) {
            $files[] = $this->contentZip->getNameIndex($i);
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
        $json = $this->contentZip->getFromName($fileName);

        $properties = json_decode($json, true);

        if ($key) {
            $properties = Arr::get($properties, $key);
        }

        return $properties;
    }

    public function __destruct()
    {
        $this->contentZip->close();

        unlink($this->tempFile);
    }
}
