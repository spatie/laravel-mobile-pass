<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\LoyaltyClassValidator;

class LoyaltyPassClass extends GooglePassClass
{
    protected ?LocalizedString $programName = null;

    protected ?Image $programLogo = null;

    protected ?LocalizedString $rewardsTier = null;

    protected ?LocalizedString $rewardsTierLabel = null;

    protected ?LocalizedString $accountNameLabel = null;

    protected ?LocalizedString $accountIdLabel = null;

    protected static function resourceName(): string
    {
        return 'loyaltyClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new LoyaltyClassValidator;
    }

    public function setProgramName(LocalizedString|string $programName, string $language = 'en-US'): self
    {
        if ($programName instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->programName = $programName instanceof LocalizedString
            ? $programName
            : LocalizedString::of($programName, $language);

        return $this;
    }

    public function getProgramName(): ?string
    {
        return $this->programName?->defaultValue;
    }

    public function setProgramLogoUrl(string $url): self
    {
        $this->programLogo = Image::fromUrl($url);

        return $this;
    }

    public function setRewardsTier(LocalizedString|string $rewardsTier, string $language = 'en-US'): self
    {
        if ($rewardsTier instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->rewardsTier = $rewardsTier instanceof LocalizedString
            ? $rewardsTier
            : LocalizedString::of($rewardsTier, $language);

        return $this;
    }

    public function setRewardsTierLabel(LocalizedString|string $rewardsTierLabel, string $language = 'en-US'): self
    {
        if ($rewardsTierLabel instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->rewardsTierLabel = $rewardsTierLabel instanceof LocalizedString
            ? $rewardsTierLabel
            : LocalizedString::of($rewardsTierLabel, $language);

        return $this;
    }

    public function setAccountNameLabel(LocalizedString|string $accountNameLabel, string $language = 'en-US'): self
    {
        if ($accountNameLabel instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->accountNameLabel = $accountNameLabel instanceof LocalizedString
            ? $accountNameLabel
            : LocalizedString::of($accountNameLabel, $language);

        return $this;
    }

    public function setAccountIdLabel(LocalizedString|string $accountIdLabel, string $language = 'en-US'): self
    {
        if ($accountIdLabel instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->accountIdLabel = $accountIdLabel instanceof LocalizedString
            ? $accountIdLabel
            : LocalizedString::of($accountIdLabel, $language);

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return $this->filterEmpty([
            'issuerName' => $this->issuerName,
            'programName' => $this->programName?->defaultValue,
            'localizedProgramName' => $this->programName?->toArray(),
            'programLogo' => $this->programLogo?->toArray(),
            'rewardsTier' => $this->rewardsTier?->defaultValue,
            'localizedRewardsTier' => $this->rewardsTier?->toArray(),
            'rewardsTierLabel' => $this->rewardsTierLabel?->defaultValue,
            'localizedRewardsTierLabel' => $this->rewardsTierLabel?->toArray(),
            'accountNameLabel' => $this->accountNameLabel?->defaultValue,
            'localizedAccountNameLabel' => $this->accountNameLabel?->toArray(),
            'accountIdLabel' => $this->accountIdLabel?->defaultValue,
            'localizedAccountIdLabel' => $this->accountIdLabel?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
            'reviewStatus' => $this->reviewStatus,
        ]);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        $this->hydrateCommonFields($payload);

        $this->programName = $this->hydrateLocalizedString($payload, 'localizedProgramName', 'programName');
        $this->programLogo = $this->hydrateImage($payload, 'programLogo');
        $this->rewardsTier = $this->hydrateLocalizedString($payload, 'localizedRewardsTier', 'rewardsTier');
        $this->rewardsTierLabel = $this->hydrateLocalizedString($payload, 'localizedRewardsTierLabel', 'rewardsTierLabel');
        $this->accountNameLabel = $this->hydrateLocalizedString($payload, 'localizedAccountNameLabel', 'accountNameLabel');
        $this->accountIdLabel = $this->hydrateLocalizedString($payload, 'localizedAccountIdLabel', 'accountIdLabel');
    }
}
