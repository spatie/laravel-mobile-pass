<?php

namespace Spatie\LaravelMobilePass\Support;

use ZipArchive;

class PkPassReader
{
    protected string $contentString;
    protected ZipArchive $contentZip;

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
        $this->contentZip = new ZipArchive();

        $tempFile = tempnam(sys_get_temp_dir(), 'zip');
        file_put_contents($tempFile, $zipString); // Write the ZIP data

        $this->contentZip->open($tempFile);
    }

    public function manifest(): array
    {
        $manifestJson = $this->contentZip->getFromName('manifest.json');

        return json_decode($manifestJson, true);
    }

    public function pass(): array
    {
        $passJson = $this->contentZip->getFromName('pass.json');

        return json_decode($passJson, true);

    }

    public function __destruct()
    {
        $this->contentZip->close();

    }
}
