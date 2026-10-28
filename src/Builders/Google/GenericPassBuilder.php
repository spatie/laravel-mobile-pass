<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GenericObjectValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class GenericPassBuilder extends GooglePassBuilder
{
    protected PassType $type = PassType::Generic;

    protected ?LocalizedString $header = null;

    protected ?LocalizedString $cardTitle = null;

    protected ?LocalizedString $subheader = null;

    protected ?bool $expiryNotificationEnabled = null;

    protected ?Image $logo = null;

    protected ?Image $hero = null;

    protected ?string $backgroundColor = null;

    protected static function validator(): GooglePassObjectValidator
    {
        return new GenericObjectValidator;
    }

    protected static function classResource(): string
    {
        return 'genericClass';
    }

    protected static function objectResource(): string
    {
        return 'genericObject';
    }

    public function setHeader(LocalizedString|string $value, string $language = 'en-US'): self
    {
        $this->header = $this->localized($value, $language);

        return $this;
    }

    public function setCardTitle(LocalizedString|string $value, string $language = 'en-US'): self
    {
        $this->cardTitle = $this->localized($value, $language);

        return $this;
    }

    public function setSubheader(LocalizedString|string $value, string $language = 'en-US'): self
    {
        $this->subheader = $this->localized($value, $language);

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

    public function setBackgroundColor(string $hex): self
    {
        $this->backgroundColor = $hex;

        return $this;
    }

    public function setExpiryNotificationEnabled(bool $enabled): self
    {
        $this->expiryNotificationEnabled = $enabled;

        return $this;
    }

    protected function localized(LocalizedString|string $value, string $language): LocalizedString
    {
        if ($value instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        return $value instanceof LocalizedString
            ? $value
            : LocalizedString::of($value, $language);
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        $notifications = $this->compileNotifications();

        return $this->filterEmpty([
            'header' => $this->header?->toArray(),
            'cardTitle' => $this->cardTitle?->toArray(),
            'subheader' => $this->subheader?->toArray(),
            'logo' => $this->logo?->toArray(),
            'heroImage' => $this->hero?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
            'notifications' => $notifications,
        ]);
    }

    /** @return array<string, mixed>|null */
    protected function compileNotifications(): ?array
    {
        if ($this->expiryNotificationEnabled === null) {
            return null;
        }

        return [
            'expiryNotification' => [
                'enableNotification' => $this->expiryNotificationEnabled,
            ],
        ];
    }
}
