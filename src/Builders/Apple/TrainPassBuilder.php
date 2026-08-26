<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Enums\TransitType;

class TrainPassBuilder extends BoardingPassBuilder
{
    protected ?TransitType $transitType = TransitType::Train;

    protected ?string $carNumber = null;

    protected ?string $departurePlatform = null;

    protected ?string $departureStationName = null;

    protected ?string $destinationPlatform = null;

    protected ?string $destinationStationName = null;

    /** The number of the passenger car. A train car is also called a carriage, wagon, coach, or bogie in some countries. */
    public function setCarNumber(string $carNumber): static
    {
        $this->carNumber = $carNumber;

        return $this;
    }

    /** The name of the departure platform, such as "A". Don't include the word "platform". */
    public function setDeparturePlatform(string $departurePlatform): static
    {
        $this->departurePlatform = $departurePlatform;

        return $this;
    }

    /** The name of the departure station, such as "1st Street Station". */
    public function setDepartureStationName(string $departureStationName): static
    {
        $this->departureStationName = $departureStationName;

        return $this;
    }

    /** The name of the destination platform, such as "A". Don't include the word "platform". */
    public function setDestinationPlatform(string $destinationPlatform): static
    {
        $this->destinationPlatform = $destinationPlatform;

        return $this;
    }

    /** The name of the destination station, such as "1st Street Station". */
    public function setDestinationStationName(string $destinationStationName): static
    {
        $this->destinationStationName = $destinationStationName;

        return $this;
    }

    protected function compileSemantics(): array
    {
        return array_merge(
            parent::compileSemantics(),
            array_filter([
                'carNumber' => $this->carNumber,
                'departurePlatform' => $this->departurePlatform,
                'departureStationName' => $this->departureStationName,
                'destinationPlatform' => $this->destinationPlatform,
                'destinationStationName' => $this->destinationStationName,
            ], fn ($value) => $value !== null),
        );
    }

    protected function uncompileSemantics(): void
    {
        parent::uncompileSemantics();

        $semantics = $this->data['semantics'] ?? [];

        $this->carNumber = $semantics['carNumber'] ?? null;
        $this->departurePlatform = $semantics['departurePlatform'] ?? null;
        $this->departureStationName = $semantics['departureStationName'] ?? null;
        $this->destinationPlatform = $semantics['destinationPlatform'] ?? null;
        $this->destinationStationName = $semantics['destinationStationName'] ?? null;
    }
}
