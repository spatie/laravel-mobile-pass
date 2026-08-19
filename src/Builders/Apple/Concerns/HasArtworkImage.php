<?php

namespace Spatie\LaravelMobilePass\Builders\Apple\Concerns;

use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;

trait HasArtworkImage
{
    public function setArtworkImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self
    {
        $this->images['artwork'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteArtworkImage(string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self
    {
        $this->images['artwork'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    public function setLocaleArtworkImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): self
    {
        $this->locales[$language]['images']['artwork'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteLocaleArtworkImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): self
    {
        $this->locales[$language]['images']['artwork'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }
}
