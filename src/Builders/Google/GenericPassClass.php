<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GenericClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;

class GenericPassClass extends GooglePassClass
{
    protected ?LocalizedString $cardTitle = null;

    protected ?LocalizedString $subheader = null;

    protected ?LocalizedString $header = null;

    protected ?Image $logo = null;

    protected ?Image $hero = null;

    protected static function resourceName(): string
    {
        return 'genericClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new GenericClassValidator;
    }

    public function setCardTitle(LocalizedString|string $value, string $language = 'en-US'): self
    {
        if ($value instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->cardTitle = $value instanceof LocalizedString
            ? $value
            : LocalizedString::of($value, $language);

        return $this;
    }

    public function getCardTitle(): ?string
    {
        return $this->cardTitle?->defaultValue;
    }

    public function setSubheader(LocalizedString|string $value, string $language = 'en-US'): self
    {
        if ($value instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->subheader = $value instanceof LocalizedString
            ? $value
            : LocalizedString::of($value, $language);

        return $this;
    }

    public function setHeader(LocalizedString|string $value, string $language = 'en-US'): self
    {
        if ($value instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->header = $value instanceof LocalizedString
            ? $value
            : LocalizedString::of($value, $language);

        return $this;
    }

    public function setLogoUrl(string $url): self
    {
        $this->logo = Image::fromUrl($url);

        return $this;
    }

    public function setHeroImageUrl(string $url): self
    {
        $this->hero = Image::fromUrl($url);

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return $this->filterEmpty([
            'issuerName' => $this->issuerName,
            'cardTitle' => $this->cardTitle?->toArray(),
            'subheader' => $this->subheader?->toArray(),
            'header' => $this->header?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
            'logo' => $this->logo?->toArray(),
            'heroImage' => $this->hero?->toArray(),
            'reviewStatus' => $this->reviewStatus,
        ]);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        $this->hydrateCommonFields($payload);

        $this->cardTitle = $this->hydrateLocalizedString($payload, 'cardTitle');
        $this->subheader = $this->hydrateLocalizedString($payload, 'subheader');
        $this->header = $this->hydrateLocalizedString($payload, 'header');
        $this->logo = $this->hydrateImage($payload, 'logo');
        $this->hero = $this->hydrateImage($payload, 'heroImage');
    }
}
