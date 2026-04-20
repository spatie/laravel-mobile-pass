<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Carbon\Carbon;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Validators\BoardingClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;

class BoardingPassClass extends GooglePassClass
{
    protected ?Carbon $localScheduledDepartureDateTime = null;

    protected ?string $airlineCode = null;

    protected ?string $flightNumber = null;

    protected ?string $originAirportCode = null;

    protected ?string $destinationAirportCode = null;

    protected ?Image $logo = null;

    protected ?Image $hero = null;

    protected ?string $backgroundColor = null;

    protected static function resourceName(): string
    {
        return 'flightClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new BoardingClassValidator;
    }

    public function setLocalScheduledDepartureDateTime(Carbon $dateTime): self
    {
        $this->localScheduledDepartureDateTime = $dateTime;

        return $this;
    }

    public function setAirlineCode(string $airlineCode): self
    {
        $this->airlineCode = $airlineCode;

        return $this;
    }

    public function setFlightNumber(string $flightNumber): self
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    public function getFlightNumber(): ?string
    {
        return $this->flightNumber;
    }

    public function setOriginAirportCode(string $iataCode): self
    {
        $this->originAirportCode = $iataCode;

        return $this;
    }

    public function setDestinationAirportCode(string $iataCode): self
    {
        $this->destinationAirportCode = $iataCode;

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
        $flightHeader = $this->filterEmpty([
            'carrier' => $this->airlineCode ? ['airlineCode' => $this->airlineCode] : null,
            'flightNumber' => $this->flightNumber,
        ]);

        return $this->filterEmpty([
            'issuerName' => $this->issuerName,
            'localScheduledDepartureDateTime' => $this->localScheduledDepartureDateTime?->toIso8601String(),
            'flightHeader' => $flightHeader,
            'origin' => $this->originAirportCode ? ['airportIataCode' => $this->originAirportCode] : null,
            'destination' => $this->destinationAirportCode ? ['airportIataCode' => $this->destinationAirportCode] : null,
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

        if (isset($payload['localScheduledDepartureDateTime'])) {
            $this->localScheduledDepartureDateTime = Carbon::parse((string) $payload['localScheduledDepartureDateTime']);
        }

        if (isset($payload['flightHeader']['carrier']['airlineCode'])) {
            $this->airlineCode = (string) $payload['flightHeader']['carrier']['airlineCode'];
        }

        if (isset($payload['flightHeader']['flightNumber'])) {
            $this->flightNumber = (string) $payload['flightHeader']['flightNumber'];
        }

        if (isset($payload['origin']['airportIataCode'])) {
            $this->originAirportCode = (string) $payload['origin']['airportIataCode'];
        }

        if (isset($payload['destination']['airportIataCode'])) {
            $this->destinationAirportCode = (string) $payload['destination']['airportIataCode'];
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
    }
}
