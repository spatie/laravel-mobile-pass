<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\LoyaltyClassValidator;

class LoyaltyPassClass extends GooglePassClass
{
    protected ?string $programName = null;

    protected ?Image $programLogo = null;

    protected ?string $rewardsTier = null;

    protected ?string $rewardsTierLabel = null;

    protected ?string $accountNameLabel = null;

    protected ?string $accountIdLabel = null;

    protected ?string $backgroundColor = null;

    protected static function resourceName(): string
    {
        return 'loyaltyClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new LoyaltyClassValidator;
    }

    public function setProgramName(string $programName): self
    {
        $this->programName = $programName;

        return $this;
    }

    public function getProgramName(): ?string
    {
        return $this->programName;
    }

    public function setProgramLogoUrl(string $url): self
    {
        $this->programLogo = Image::fromUrl($url);

        return $this;
    }

    public function setRewardsTier(string $rewardsTier): self
    {
        $this->rewardsTier = $rewardsTier;

        return $this;
    }

    public function setRewardsTierLabel(string $rewardsTierLabel): self
    {
        $this->rewardsTierLabel = $rewardsTierLabel;

        return $this;
    }

    public function setAccountNameLabel(string $accountNameLabel): self
    {
        $this->accountNameLabel = $accountNameLabel;

        return $this;
    }

    public function setAccountIdLabel(string $accountIdLabel): self
    {
        $this->accountIdLabel = $accountIdLabel;

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
        return $this->filterEmpty([
            'issuerName' => $this->issuerName,
            'programName' => $this->programName,
            'programLogo' => $this->programLogo?->toArray(),
            'rewardsTier' => $this->rewardsTier,
            'rewardsTierLabel' => $this->rewardsTierLabel,
            'accountNameLabel' => $this->accountNameLabel,
            'accountIdLabel' => $this->accountIdLabel,
            'hexBackgroundColor' => $this->backgroundColor,
            'reviewStatus' => $this->reviewStatus,
        ]);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        $this->hydrateCommonFields($payload);

        if (isset($payload['programName'])) {
            $this->programName = (string) $payload['programName'];
        }

        if (isset($payload['programLogo']['sourceUri']['uri'])) {
            $this->programLogo = Image::fromUrl((string) $payload['programLogo']['sourceUri']['uri']);
        }

        if (isset($payload['rewardsTier'])) {
            $this->rewardsTier = (string) $payload['rewardsTier'];
        }

        if (isset($payload['rewardsTierLabel'])) {
            $this->rewardsTierLabel = (string) $payload['rewardsTierLabel'];
        }

        if (isset($payload['accountNameLabel'])) {
            $this->accountNameLabel = (string) $payload['accountNameLabel'];
        }

        if (isset($payload['accountIdLabel'])) {
            $this->accountIdLabel = (string) $payload['accountIdLabel'];
        }

        if (isset($payload['hexBackgroundColor'])) {
            $this->backgroundColor = (string) $payload['hexBackgroundColor'];
        }
    }
}
