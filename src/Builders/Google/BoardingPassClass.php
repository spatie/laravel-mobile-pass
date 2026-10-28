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

    public function getAirlineCode(): ?string
    {
        return $this->airlineCode;
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

    /**
     * flightClass has no top-level logo: this sets the carrier's airline logo
     * (flightHeader.carrier.airlineLogo), shown at the top of the card.
     */
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
        $flightHeader = $this->filterEmpty([
            'carrier' => $this->filterEmpty([
                'carrierIataCode' => $this->airlineCode,
                'airlineLogo' => $this->logo?->toArray(),
            ]),
            'flightNumber' => $this->flightNumber,
        ]);

        return $this->filterEmpty([
            'issuerName' => $this->issuerName,
            // Google wants the local airport time without a UTC offset and rejects values that carry one.
            'localScheduledDepartureDateTime' => $this->localScheduledDepartureDateTime?->format('Y-m-d\TH:i:s'),
            'flightHeader' => $flightHeader,
            'origin' => $this->originAirportCode ? ['airportIataCode' => $this->originAirportCode] : null,
            'destination' => $this->destinationAirportCode ? ['airportIataCode' => $this->destinationAirportCode] : null,
            'heroImage' => $this->hero?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
        ]);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        $this->hydrateCommonFields($payload);

        if (isset($payload['localScheduledDepartureDateTime'])) {
            $this->localScheduledDepartureDateTime = Carbon::parse((string) $payload['localScheduledDepartureDateTime']);
        }

        // `airlineCode` is what earlier versions of this package (incorrectly) stored.
        $carrierCode = $payload['flightHeader']['carrier']['carrierIataCode']
            ?? $payload['flightHeader']['carrier']['airlineCode']
            ?? null;

        if ($carrierCode !== null) {
            $this->airlineCode = (string) $carrierCode;
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

        // A top-level `logo` is what earlier versions of this package (incorrectly) stored.
        $this->logo = $this->hydrateImage($payload['flightHeader']['carrier'] ?? [], 'airlineLogo')
            ?? $this->hydrateImage($payload, 'logo');
        $this->hero = $this->hydrateImage($payload, 'heroImage');
    }
}
