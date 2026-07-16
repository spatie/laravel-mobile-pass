<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\OfferClassValidator;

class OfferPassClass extends GooglePassClass
{
    protected ?LocalizedString $title = null;

    protected ?string $redemptionChannel = null;

    protected ?LocalizedString $provider = null;

    protected ?LocalizedString $details = null;

    protected ?LocalizedString $finePrint = null;

    protected ?Image $logo = null;

    protected static function resourceName(): string
    {
        return 'offerClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new OfferClassValidator;
    }

    public function setTitle(LocalizedString|string $title, string $language = 'en-US'): self
    {
        if ($title instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->title = $title instanceof LocalizedString
            ? $title
            : LocalizedString::of($title, $language);

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title?->defaultValue;
    }

    public function setRedemptionChannel(string $redemptionChannel): self
    {
        $this->redemptionChannel = $redemptionChannel;

        return $this;
    }

    public function setProvider(LocalizedString|string $provider, string $language = 'en-US'): self
    {
        if ($provider instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->provider = $provider instanceof LocalizedString
            ? $provider
            : LocalizedString::of($provider, $language);

        return $this;
    }

    public function setDetails(LocalizedString|string $details, string $language = 'en-US'): self
    {
        if ($details instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->details = $details instanceof LocalizedString
            ? $details
            : LocalizedString::of($details, $language);

        return $this;
    }

    public function setFinePrint(LocalizedString|string $finePrint, string $language = 'en-US'): self
    {
        if ($finePrint instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->finePrint = $finePrint instanceof LocalizedString
            ? $finePrint
            : LocalizedString::of($finePrint, $language);

        return $this;
    }

    public function setLogoUrl(string $url): self
    {
        $this->logo = Image::fromUrl($url);

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return $this->filterEmpty([
            'issuerName' => $this->issuerName,
            'title' => $this->title?->defaultValue,
            'localizedTitle' => $this->title?->toArray(),
            'redemptionChannel' => $this->redemptionChannel,
            'provider' => $this->provider?->defaultValue,
            'localizedProvider' => $this->provider?->toArray(),
            'details' => $this->details?->defaultValue,
            'localizedDetails' => $this->details?->toArray(),
            'finePrint' => $this->finePrint?->defaultValue,
            'localizedFinePrint' => $this->finePrint?->toArray(),
            'logo' => $this->logo?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
            'reviewStatus' => $this->reviewStatus,
        ]);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        $this->hydrateCommonFields($payload);

        $this->title = $this->hydrateLocalizedString($payload, 'localizedTitle', 'title');
        $this->provider = $this->hydrateLocalizedString($payload, 'localizedProvider', 'provider');
        $this->details = $this->hydrateLocalizedString($payload, 'localizedDetails', 'details');
        $this->finePrint = $this->hydrateLocalizedString($payload, 'localizedFinePrint', 'finePrint');

        if (isset($payload['redemptionChannel'])) {
            $this->redemptionChannel = (string) $payload['redemptionChannel'];
        }

        $this->logo = $this->hydrateImage($payload, 'logo');
    }
}
