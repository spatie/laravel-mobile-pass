<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GenericClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;

/**
 * Google's genericClass is a thin template: unlike the other class types it has
 * no issuer name, card title, header, images, colors, locations or reviewStatus.
 * Those display fields live on the generic object, see GenericPassBuilder.
 */
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

    protected function reviewStatus(): ?string
    {
        return null;
    }

    /**
     * @deprecated genericClass has no issuerName field, so this value is not sent to Google.
     */
    public function setIssuerName(string $issuerName): static
    {
        return parent::setIssuerName($issuerName);
    }

    /**
     * @deprecated genericClass has no hexBackgroundColor field, so this value is not sent
     *             to Google. Use GenericPassBuilder::setBackgroundColor() instead.
     */
    public function setBackgroundColor(string $hex): static
    {
        return parent::setBackgroundColor($hex);
    }

    /**
     * @deprecated genericClass has no locations field, so these are not sent to Google.
     */
    public function addLocation(float $latitude, float $longitude): static
    {
        return parent::addLocation($latitude, $longitude);
    }

    /**
     * @deprecated genericClass has no such field, so this value is not sent to Google.
     *             Use GenericPassBuilder::setCardTitle() on the object instead.
     */
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

    /**
     * @deprecated genericClass has no such field, so this value is not sent to Google.
     *             Use GenericPassBuilder::setSubheader() on the object instead.
     */
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

    /**
     * @deprecated genericClass has no such field, so this value is not sent to Google.
     *             Use GenericPassBuilder::setHeader() on the object instead.
     */
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

    /**
     * @deprecated genericClass has no such field, so this value is not sent to Google.
     *             Use GenericPassBuilder::setLogoUrl() on the object instead.
     */
    public function setLogoUrl(string $url): self
    {
        $this->logo = Image::fromUrl($url);

        return $this;
    }

    /**
     * @deprecated genericClass has no such field, so this value is not sent to Google.
     *             Use GenericPassBuilder::setHeroImageUrl() on the object instead.
     */
    public function setHeroImageUrl(string $url): self
    {
        $this->hero = Image::fromUrl($url);

        return $this;
    }

    /**
     * The deprecated display setters above are intentionally not compiled:
     * Google rejects them on genericClass.
     *
     * @return array<string, mixed>
     */
    protected function compileData(): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    protected function compileModules(): array
    {
        $modules = parent::compileModules();

        unset($modules['locations']);

        return $modules;
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
