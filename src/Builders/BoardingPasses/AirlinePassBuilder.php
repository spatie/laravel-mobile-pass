<?php

namespace Spatie\LaravelMobilePass\Builders\BoardingPasses;

use Spatie\LaravelMobilePass\Enums\TransitType;

class AirlinePassBuilder extends BoardingPassBuilder
{
    protected ?TransitType $transitType = TransitType::Air;

    protected ?string $airlineCode = null;

    protected ?string $flightCode = null;

    protected ?string $flightNumber = null;

    protected ?string $departureGate = null;

    protected ?string $departureTerminal = null;

    protected ?string $departureAirportCode = null;

    protected ?string $departureAirportName = null;

    protected ?string $destinationAirportName = null;

    protected ?string $destinationAirportCode = null;

    protected ?string $destinationGate = null;

    protected ?string $destinationTerminal = null;

    /**
     * The IATA airline code, such as EX for flightCode EX123.
     */
    public function setAirlineCode(string $airlineCode): self
    {
        $this->airlineCode = $airlineCode;

        return $this;
    }

    /**
     * The IATA airport code for the departure airport, such as MPM or LHR.
     */
    public function setDepartureAirportCode(string $departureAirportCode): self
    {
        $this->departureAirportCode = $departureAirportCode;

        return $this;
    }

    /**
     * The full name of the departure airport, such as Maputo International Airport.
     */
    public function setDepartureAirportName(string $departureAirportName): self
    {
        $this->departureAirportName = $departureAirportName;

        return $this;
    }

    /**
     * The gate number or letters of the departure gate, such as 1A. Don’t include the word gate.
     */
    public function setDepartureGate(string $departureGate): self
    {
        $this->departureGate = $departureGate;

        return $this;
    }

    /**
     * The name or letter of the departure terminal, such as A. Don’t include the word terminal.
     */
    public function setDepartureTerminal(string $departureTerminal): self
    {
        $this->departureTerminal = $departureTerminal;

        return $this;
    }

    /**
     * The full name of the destination airport, such as London Heathrow.
     */
    public function setDestinationAirportName(string $destinationAirportName): self
    {
        $this->destinationAirportName = $destinationAirportName;

        return $this;
    }

    /**
     * The IATA airport code for the destination airport, such as MPM or LHR.
     */
    public function setDestinationAirportCode(string $destinationAirportCode): self
    {
        $this->destinationAirportCode = $destinationAirportCode;

        return $this;
    }

    /**
     * The gate number or letter of the destination gate, such as 1A. Don’t include the word gate.
     */
    public function setDestinationGate(string $destinationGate): self
    {
        $this->destinationGate = $destinationGate;

        return $this;
    }

    /**
     * The terminal name or letter of the destination terminal, such as A. Don’t include the word terminal.
     */
    public function setDestinationTerminal(string $destinationTerminal): self
    {
        $this->destinationTerminal = $destinationTerminal;

        return $this;
    }

    /**
     * The IATA flight code, such as EX123.
     */
    public function setFlightCode(string $flightCode): self
    {
        $this->flightCode = $flightCode;

        return $this;
    }

    /**
     * The numeric portion of the IATA flight code, such as 123 for flightCode EX123.
     */
    public function setFlightNumber(string $flightNumber): self
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }
}
