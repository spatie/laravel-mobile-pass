<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Carbon\Carbon;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\EventTicketClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;

class EventTicketPassClass extends GooglePassClass
{
    protected ?LocalizedString $eventName = null;

    protected ?LocalizedString $venueName = null;

    protected ?LocalizedString $venueAddress = null;

    protected ?Carbon $startDate = null;

    protected ?Image $logo = null;

    protected ?Image $hero = null;

    protected static function resourceName(): string
    {
        return 'eventTicketClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new EventTicketClassValidator;
    }

    public function setEventName(LocalizedString|string $value, string $language = 'en-US'): self
    {
        if ($value instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->eventName = $value instanceof LocalizedString
            ? $value
            : LocalizedString::of($value, $language);

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName?->defaultValue;
    }

    public function setVenueName(LocalizedString|string $value, string $language = 'en-US'): self
    {
        if ($value instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->venueName = $value instanceof LocalizedString
            ? $value
            : LocalizedString::of($value, $language);

        return $this;
    }

    public function setVenueAddress(LocalizedString|string $value, string $language = 'en-US'): self
    {
        if ($value instanceof LocalizedString && $language !== 'en-US') {
            throw new \InvalidArgumentException(
                'Do not pass $language when $value is already a LocalizedString — set the language via LocalizedString::of() instead.'
            );
        }

        $this->venueAddress = $value instanceof LocalizedString
            ? $value
            : LocalizedString::of($value, $language);

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

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        $venue = $this->filterEmpty([
            'name' => $this->venueName?->toArray(),
            'address' => $this->venueAddress?->toArray(),
        ]);

        return $this->filterEmpty([
            'issuerName' => $this->issuerName,
            'eventName' => $this->eventName?->toArray(),
            'venue' => $venue,
            'dateTime' => $this->startDate ? ['start' => $this->startDate->toIso8601String()] : null,
            'logo' => $this->logo?->toArray(),
            'heroImage' => $this->hero?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
            'reviewStatus' => $this->reviewStatus,
        ]);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        $this->hydrateCommonFields($payload);

        $this->eventName = $this->hydrateLocalizedString($payload, 'eventName');
        $this->venueName = $this->hydrateLocalizedString($payload['venue'] ?? [], 'name');
        $this->venueAddress = $this->hydrateLocalizedString($payload['venue'] ?? [], 'address');

        if (isset($payload['dateTime']['start'])) {
            $this->startDate = Carbon::parse((string) $payload['dateTime']['start']);
        }

        $this->logo = $this->hydrateImage($payload, 'logo');
        $this->hero = $this->hydrateImage($payload, 'heroImage');
    }
}
