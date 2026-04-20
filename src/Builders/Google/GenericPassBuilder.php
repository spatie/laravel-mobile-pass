<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

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

    public function setHeader(string $value, string $language = 'en-US'): self
    {
        $this->header = LocalizedString::of($value, $language);

        return $this;
    }

    public function setCardTitle(string $value, string $language = 'en-US'): self
    {
        $this->cardTitle = LocalizedString::of($value, $language);

        return $this;
    }

    public function setSubheader(string $value, string $language = 'en-US'): self
    {
        $this->subheader = LocalizedString::of($value, $language);

        return $this;
    }

    public function setExpiryNotificationEnabled(bool $enabled): self
    {
        $this->expiryNotificationEnabled = $enabled;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        $notifications = $this->compileNotifications();

        return $this->filterEmpty([
            'header' => $this->header?->toArray(),
            'cardTitle' => $this->cardTitle?->toArray(),
            'subheader' => $this->subheader?->toArray(),
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
