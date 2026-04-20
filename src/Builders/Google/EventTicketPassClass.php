<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Carbon\Carbon;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\EventTicketClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;

class EventTicketPassClass extends GooglePassClass
{
    protected ?string $issuerName = null;

    protected ?LocalizedString $eventName = null;

    protected ?LocalizedString $venueName = null;

    protected ?LocalizedString $venueAddress = null;

    protected ?Carbon $startDate = null;

    protected ?Image $logo = null;

    protected ?Image $hero = null;

    protected ?string $backgroundColor = null;

    protected static function resourceName(): string
    {
        return 'eventTicketClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new EventTicketClassValidator;
    }

    public function setIssuerName(string $issuerName): self
    {
        $this->issuerName = $issuerName;

        return $this;
    }

    public function setEventName(string $value, string $language = 'en-US'): self
    {
        $this->eventName = LocalizedString::of($value, $language);

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName?->defaultValue;
    }

    public function setVenueName(string $value, string $language = 'en-US'): self
    {
        $this->venueName = LocalizedString::of($value, $language);

        return $this;
    }

    public function setVenueAddress(string $value, string $language = 'en-US'): self
    {
        $this->venueAddress = LocalizedString::of($value, $language);

        return $this;
    }

    public function setStartDate(Carbon $startDate): self
    {
        $this->startDate = $startDate;

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

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        $venue = array_filter([
            'name' => $this->venueName?->toArray(),
            'address' => $this->venueAddress?->toArray(),
        ]);

        return array_filter([
            'issuerName' => $this->issuerName,
            'eventName' => $this->eventName?->toArray(),
            'venue' => $venue !== [] ? $venue : null,
            'dateTime' => $this->startDate ? ['start' => $this->startDate->toIso8601String()] : null,
            'logo' => $this->logo?->toArray(),
            'heroImage' => $this->hero?->toArray(),
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

        if (isset($payload['eventName']['defaultValue']['value'])) {
            $this->eventName = LocalizedString::of(
                (string) $payload['eventName']['defaultValue']['value'],
                (string) ($payload['eventName']['defaultValue']['language'] ?? 'en-US'),
            );
        }

        if (isset($payload['venue']['name']['defaultValue']['value'])) {
            $this->venueName = LocalizedString::of((string) $payload['venue']['name']['defaultValue']['value']);
        }

        if (isset($payload['venue']['address']['defaultValue']['value'])) {
            $this->venueAddress = LocalizedString::of((string) $payload['venue']['address']['defaultValue']['value']);
        }

        if (isset($payload['dateTime']['start'])) {
            $this->startDate = Carbon::parse((string) $payload['dateTime']['start']);
        }

        if (isset($payload['logo']['sourceUri']['uri'])) {
            $this->logo = Image::fromUrl((string) $payload['logo']['sourceUri']['uri']);
        }

        if (isset($payload['heroImage']['sourceUri']['uri'])) {
            $this->hero = Image::fromUrl((string) $payload['heroImage']['sourceUri']['uri']);
        }

        if (isset($payload['hexBackgroundColor'])) {
            $this->backgroundColor = (string) $payload['hexBackgroundColor'];
        }

        if (isset($payload['reviewStatus'])) {
            $this->reviewStatus = (string) $payload['reviewStatus'];
        }
    }
}
