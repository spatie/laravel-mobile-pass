<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Spatie\LaravelMobilePass\Enums\PassengerCapability;
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

    /** @var array<int, string>|null */
    protected ?array $passengerAirlineSsrs = null;

    /** @var array<int, PassengerCapability>|null */
    protected ?array $passengerCapabilities = null;

    /** @var array<int, string>|null */
    protected ?array $passengerInformationSsrs = null;

    /** @var array<int, string>|null */
    protected ?array $passengerServiceSsrs = null;

    /**
     * The IATA airline code, such as EX for flightCode EX123.
     */
    public function setAirlineCode(string $airlineCode): static
    {
        $this->airlineCode = $airlineCode;

        return $this;
    }

    /**
     * The IATA airport code for the departure airport, such as MPM or LHR.
     */
    public function setDepartureAirportCode(string $departureAirportCode): static
    {
        $this->departureAirportCode = $departureAirportCode;

        return $this;
    }

    /**
     * The full name of the departure airport, such as Maputo International Airport.
     */
    public function setDepartureAirportName(string $departureAirportName): static
    {
        $this->departureAirportName = $departureAirportName;

        return $this;
    }

    /**
     * The gate number or letters of the departure gate, such as 1A. Don’t include the word gate.
     */
    public function setDepartureGate(string $departureGate): static
    {
        $this->departureGate = $departureGate;

        return $this;
    }

    /**
     * The name or letter of the departure terminal, such as A. Don’t include the word terminal.
     */
    public function setDepartureTerminal(string $departureTerminal): static
    {
        $this->departureTerminal = $departureTerminal;

        return $this;
    }

    /**
     * The full name of the destination airport, such as London Heathrow.
     */
    public function setDestinationAirportName(string $destinationAirportName): static
    {
        $this->destinationAirportName = $destinationAirportName;

        return $this;
    }

    /**
     * The IATA airport code for the destination airport, such as MPM or LHR.
     */
    public function setDestinationAirportCode(string $destinationAirportCode): static
    {
        $this->destinationAirportCode = $destinationAirportCode;

        return $this;
    }

    /**
     * The gate number or letter of the destination gate, such as 1A. Don’t include the word gate.
     */
    public function setDestinationGate(string $destinationGate): static
    {
        $this->destinationGate = $destinationGate;

        return $this;
    }

    /**
     * The terminal name or letter of the destination terminal, such as A. Don’t include the word terminal.
     */
    public function setDestinationTerminal(string $destinationTerminal): static
    {
        $this->destinationTerminal = $destinationTerminal;

        return $this;
    }

    /**
     * The IATA flight code, such as EX123.
     */
    public function setFlightCode(string $flightCode): static
    {
        $this->flightCode = $flightCode;

        return $this;
    }

    /**
     * The numeric portion of the IATA flight code, such as 123 for flightCode EX123.
     */
    public function setFlightNumber(string $flightNumber): static
    {
        $this->flightNumber = $flightNumber;

        return $this;
    }

    /** An array of airline-specific SSRs that apply to the ticketed passenger. */
    public function setPassengerAirlineSsrs(string ...$passengerAirlineSsrs): static
    {
        $this->passengerAirlineSsrs = $passengerAirlineSsrs;

        return $this;
    }

    /** A list of capabilities the passenger has. */
    public function setPassengerCapabilities(PassengerCapability ...$passengerCapabilities): static
    {
        $this->passengerCapabilities = $passengerCapabilities;

        return $this;
    }

    /** An array of IATA information SSRs that apply to the ticketed passenger. */
    public function setPassengerInformationSsrs(string ...$passengerInformationSsrs): static
    {
        $this->passengerInformationSsrs = $passengerInformationSsrs;

        return $this;
    }

    /** An array of IATA SSRs that apply to the ticketed passenger. */
    public function setPassengerServiceSsrs(string ...$passengerServiceSsrs): static
    {
        $this->passengerServiceSsrs = $passengerServiceSsrs;

        return $this;
    }

    protected function compileSemantics(): array
    {
        return array_merge(
            parent::compileSemantics(),
            array_filter([
                'airlineCode' => $this->airlineCode,
                'flightCode' => $this->flightCode,
                'flightNumber' => $this->flightNumber,
                'departureGate' => $this->departureGate,
                'departureTerminal' => $this->departureTerminal,
                'departureAirportCode' => $this->departureAirportCode,
                'departureAirportName' => $this->departureAirportName,
                'destinationAirportCode' => $this->destinationAirportCode,
                'destinationAirportName' => $this->destinationAirportName,
                'destinationGate' => $this->destinationGate,
                'destinationTerminal' => $this->destinationTerminal,
                'passengerAirlineSSRs' => $this->passengerAirlineSsrs,
                'passengerCapabilities' => $this->passengerCapabilities !== null
                    ? array_map(fn (PassengerCapability $c) => $c->value, $this->passengerCapabilities)
                    : null,
                'passengerInformationSSRs' => $this->passengerInformationSsrs,
                'passengerServiceSSRs' => $this->passengerServiceSsrs,
            ], fn ($value) => $value !== null),
        );
    }

    protected function uncompileSemantics(): void
    {
        parent::uncompileSemantics();

        $semantics = $this->data['semantics'] ?? [];

        $this->airlineCode = $semantics['airlineCode'] ?? null;
        $this->flightCode = $semantics['flightCode'] ?? null;
        $this->flightNumber = $semantics['flightNumber'] ?? null;
        $this->departureGate = $semantics['departureGate'] ?? null;
        $this->departureTerminal = $semantics['departureTerminal'] ?? null;
        $this->departureAirportCode = $semantics['departureAirportCode'] ?? null;
        $this->departureAirportName = $semantics['departureAirportName'] ?? null;
        $this->destinationAirportCode = $semantics['destinationAirportCode'] ?? null;
        $this->destinationAirportName = $semantics['destinationAirportName'] ?? null;
        $this->destinationGate = $semantics['destinationGate'] ?? null;
        $this->destinationTerminal = $semantics['destinationTerminal'] ?? null;
        $this->passengerAirlineSsrs = $semantics['passengerAirlineSSRs'] ?? null;
        $this->passengerCapabilities = isset($semantics['passengerCapabilities'])
            ? array_map(fn (string $v) => PassengerCapability::from($v), $semantics['passengerCapabilities'])
            : null;
        $this->passengerInformationSsrs = $semantics['passengerInformationSSRs'] ?? null;
        $this->passengerServiceSsrs = $semantics['passengerServiceSSRs'] ?? null;
    }
}
