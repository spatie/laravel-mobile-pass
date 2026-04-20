<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\OfferClassValidator;

class OfferPassClass extends GooglePassClass
{
    protected ?string $issuerName = null;

    protected ?string $title = null;

    protected ?string $redemptionChannel = null;

    protected ?string $provider = null;

    protected ?string $details = null;

    protected ?string $finePrint = null;

    protected ?Image $logo = null;

    protected ?string $backgroundColor = null;

    protected static function resourceName(): string
    {
        return 'offerClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new OfferClassValidator;
    }

    public function setIssuerName(string $issuerName): self
    {
        $this->issuerName = $issuerName;

        return $this;
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

    public function setBackgroundColor(string $hex): self
    {
        $this->backgroundColor = $hex;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return array_filter([
            'issuerName' => $this->issuerName,
            'title' => $this->title,
            'redemptionChannel' => $this->redemptionChannel,
            'provider' => $this->provider,
            'details' => $this->details,
            'finePrint' => $this->finePrint,
            'logo' => $this->logo?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
            'reviewStatus' => $this->reviewStatus,
        ], fn ($value) => $value !== null && $value !== []);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        if (isset($payload['issuerName'])) {
            $this->issuerName = (string) $payload['issuerName'];
        }

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

        if (isset($payload['logo']['sourceUri']['uri'])) {
            $this->logo = Image::fromUrl((string) $payload['logo']['sourceUri']['uri']);
        }

        if (isset($payload['hexBackgroundColor'])) {
            $this->backgroundColor = (string) $payload['hexBackgroundColor'];
        }

        if (isset($payload['reviewStatus'])) {
            $this->reviewStatus = (string) $payload['reviewStatus'];
        }
    }
}
