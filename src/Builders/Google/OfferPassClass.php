<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\OfferClassValidator;

class OfferPassClass extends GooglePassClass
{
    protected ?string $title = null;

    protected ?string $redemptionChannel = null;

    protected ?string $provider = null;

    protected ?string $details = null;

    protected ?string $finePrint = null;

    protected ?Image $logo = null;

    protected static function resourceName(): string
    {
        return 'offerClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new OfferClassValidator;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setRedemptionChannel(string $redemptionChannel): self
    {
        $this->redemptionChannel = $redemptionChannel;

        return $this;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function setFinePrint(string $finePrint): self
    {
        $this->finePrint = $finePrint;

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
            'title' => $this->title,
            'redemptionChannel' => $this->redemptionChannel,
            'provider' => $this->provider,
            'details' => $this->details,
            'finePrint' => $this->finePrint,
            'logo' => $this->logo?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
            'reviewStatus' => $this->reviewStatus,
        ]);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        $this->hydrateCommonFields($payload);

        if (isset($payload['title'])) {
            $this->title = (string) $payload['title'];
        }

        if (isset($payload['redemptionChannel'])) {
            $this->redemptionChannel = (string) $payload['redemptionChannel'];
        }

        if (isset($payload['provider'])) {
            $this->provider = (string) $payload['provider'];
        }

        if (isset($payload['details'])) {
            $this->details = (string) $payload['details'];
        }

        if (isset($payload['finePrint'])) {
            $this->finePrint = (string) $payload['finePrint'];
        }

        $this->logo = $this->hydrateImage($payload, 'logo');
    }
}
