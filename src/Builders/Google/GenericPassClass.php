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

    public function setCardTitle(string $value, string $language = 'en-US'): self
    {
        $this->cardTitle = LocalizedString::of($value, $language);

        return $this;
    }

    public function getCardTitle(): ?string
    {
        return $this->cardTitle?->defaultValue;
    }

    public function setSubheader(string $value, string $language = 'en-US'): self
    {
        $this->subheader = LocalizedString::of($value, $language);

        return $this;
    }

    public function setHeader(string $value, string $language = 'en-US'): self
    {
        $this->header = LocalizedString::of($value, $language);

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

        if (isset($payload['cardTitle']['defaultValue']['value'])) {
            $this->cardTitle = LocalizedString::of(
                (string) $payload['cardTitle']['defaultValue']['value'],
                (string) ($payload['cardTitle']['defaultValue']['language'] ?? 'en-US'),
            );
        }

        if (isset($payload['subheader']['defaultValue']['value'])) {
            $this->subheader = LocalizedString::of(
                (string) $payload['subheader']['defaultValue']['value'],
                (string) ($payload['subheader']['defaultValue']['language'] ?? 'en-US'),
            );
        }

        if (isset($payload['header']['defaultValue']['value'])) {
            $this->header = LocalizedString::of(
                (string) $payload['header']['defaultValue']['value'],
                (string) ($payload['header']['defaultValue']['language'] ?? 'en-US'),
            );
        }

        $this->logo = $this->hydrateImage($payload, 'logo');
        $this->hero = $this->hydrateImage($payload, 'heroImage');
    }
}
