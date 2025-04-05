<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\PersonName;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\BoardingPassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\PassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\TransitType;

abstract class BoardingApplePassBuilder extends ApplePassBuilder
{
    protected PassType $type = PassType::BoardingPass;

    protected ?TransitType $transitType = null;

    protected ?Carbon $currentArrivalDate = null;

    protected ?Carbon $currentBoardingDate = null;

    protected ?Carbon $currentDepartureDate = null;

    protected ?Carbon $originalArrivalDate = null;

    protected ?Carbon $originalBoardingDate = null;

    protected ?Carbon $originalDepartureDate = null;

    protected ?string $confirmationNumber = null;

    protected ?string $boardingGroup = null;

    protected ?string $boardingSequenceNumber = null;

    protected ?Location $departureLocation = null;

    protected ?string $departureLocationDescription = null;

    protected ?Location $destinationLocation = null;

    protected ?string $destinationLocationDescription = null;

    protected ?int $durationInSeconds = null;

    protected ?string $membershipProgramName = null;

    protected ?string $membershipProgramNumber = null;

    protected ?PersonName $passengerName = null;

    protected ?string $priorityStatus = null;

    protected ?Collection $seats = null;

    protected ?string $securityScreening = null;

    protected ?bool $silenceRequested = null;

    protected ?string $transitProvider = null;

    protected ?string $transitStatus = null;

    protected ?string $transitStatusReason = null;

    protected ?string $vehicleName = null;

    protected ?string $vehicleNumber = null;

    protected ?string $vehicleType = null;

    protected static function validator(): PassValidator
    {
        return new BoardingPassValidator;
    }

    /**
     * A group number for boarding.
     */
    public function setBoardingGroup(string $boardingGroup): self
    {
        $this->boardingGroup = $boardingGroup;

        return $this;
    }

    /**
     * A sequence number for boarding.
     */
    public function setBoardingSequenceNumber(string $boardingSequenceNumber): self
    {
        $this->boardingSequenceNumber = $boardingSequenceNumber;

        return $this;
    }

    /**
     * A booking or reservation confirmation number.
     */
    public function setConfirmationNumber(string $confirmationNumber)
    {
        $this->confirmationNumber = $confirmationNumber;

        return $this;
    }

    /**
     * The updated date and time of arrival, if different from the originally scheduled date and time.
     */
    public function setCurrentArrivalDate(Carbon $currentArrivalDate): self
    {
        $this->currentArrivalDate = $currentArrivalDate;

        return $this;
    }

    /**
     * The updated date and time of boarding, if different from the originally scheduled date and time.
     */
    public function setCurrentBoardingDate(Carbon $currentBoardingDate): self
    {
        $this->currentBoardingDate = $currentBoardingDate;

        return $this;
    }

    /**
     * The updated departure date and time, if different from the originally scheduled date and time.
     */
    public function currentDepartureDate(Carbon $currentDepartureDate): self
    {
        $this->currentDepartureDate = $currentDepartureDate;

        return $this;
    }

    /**
     * An object that represents the geographic coordinates of the transit departure location, suitable for display on a map. If possible, use precise locations, which are more useful to travelers; for example, the specific location of an airport gate.
     */
    public function setDepartureLocation(Location $departureLocation): self
    {
        $this->departureLocation = $departureLocation;

        return $this;
    }

    /**
     * A brief description of the departure location. For example, for a flight departing from an airport that has a code of LHR, an appropriate description might be London, Heathrow.
     */
    public function setDepartureLocationDescription(string $departureLocationDescription): self
    {
        $this->departureLocationDescription = $departureLocationDescription;

        return $this;
    }

    /**
     * An object that represents the geographic coordinates of the transit departure location, suitable for display on a map.
     */
    public function setDestinationLocation(Location $destinationLocation): self
    {
        $this->destinationLocation = $destinationLocation;

        return $this;
    }

    /**
     * A brief description of the destination location. For example, for a flight arriving at an airport that has a code of MPM, Maputo might be an appropriate description.
     */
    public function setDestinationLocationDescription(string $destinationLocationDescription): self
    {
        $this->destinationLocationDescription = $destinationLocationDescription;

        return $this;
    }

    /**
     * The duration of the transit journey, in seconds.
     */
    public function setDuration(int $durationInSeconds): self
    {
        $this->durationInSeconds = $durationInSeconds;

        return $this;
    }

    /**
     * The name of a frequent flyer or loyalty program.
     */
    public function setMembershipProgramName(string $membershipProgramName): self
    {
        $this->membershipProgramName = $membershipProgramName;

        return $this;
    }

    /**
     * The ticketed passenger’s frequent flyer or loyalty number.
     */
    public function setMembershipProgramNumber(string $membershipProgramNumber): self
    {
        $this->membershipProgramNumber = $membershipProgramNumber;

        return $this;
    }

    /**
     * The originally scheduled date and time of arrival.
     */
    public function setOriginalArrivalDate(Carbon $originalArrivalDate): self
    {
        $this->originalArrivalDate = $originalArrivalDate;

        return $this;
    }

    /**
     * The originally scheduled date and time of boarding.
     */
    public function setOriginalBoardingDate(Carbon $originalBoardingDate): self
    {
        $this->originalBoardingDate = $originalBoardingDate;

        return $this;
    }

    /**
     * The originally scheduled date and time of departure.
     */
    public function setOriginalDepartureDate(Carbon $originalDepartureDate): self
    {
        $this->originalDepartureDate = $originalDepartureDate;

        return $this;
    }

    /**
     * An object that represents the name of the passenger.
     */
    public function setPassengerName(PersonName $passengerName): self
    {
        $this->passengerName = $passengerName;

        return $this;
    }

    /**
     * The priority status the ticketed passenger holds, such as “Gold” or “Silver”.
     */
    public function setPriorityStatus(string $priorityStatus): self
    {
        $this->priorityStatus = $priorityStatus;

        return $this;
    }

    /**
     * An object that represents the details for each seat on a transit journey.
     */
    public function setSeats(Seat ...$seat): self
    {
        $this->seats = collect($seat);

        return $this;
    }

    /**
     * The type of security screening for the ticketed passenger, such as “Priority”.
     */
    public function setSecurityScreening(string $securityScreening): self
    {
        $this->securityScreening = $securityScreening;

        return $this;
    }

    /**
     * A Boolean value that determines whether the user’s device remains silent during a transit journey. The system may override the key and determine the length of the period of silence.
     */
    public function setSilenceRequested(bool $silenceRequested): self
    {
        $this->silenceRequested = $silenceRequested;

        return $this;
    }

    /**
     * The name of the transit company.
     */
    public function setTransitProvider(string $transitProvider): self
    {
        $this->transitProvider = $transitProvider;

        return $this;
    }

    /**
     * A brief description of the current boarding status for the vessel, such as “On Time” or “Delayed”. For delayed status, provide currentBoardingDate, currentDepartureDate, and currentArrivalDate where available.
     */
    public function setTransitStatus(string $transitStatus): self
    {
        $this->transitStatus = $transitStatus;

        return $this;
    }

    /**
     * A brief description that explains the reason for the current transitStatus, such as “Thunderstorms”.
     */
    public function setTransitStatusReason(string $transitStatusReason): self
    {
        $this->transitStatusReason = $transitStatusReason;

        return $this;
    }

    /**
     * The name of the vehicle to board, such as the name of a boat.
     */
    public function setVehicleName(string $vehicleName): self
    {
        $this->vehicleName = $vehicleName;

        return $this;
    }

    /**
     * The identifier of the vehicle to board, such as the aircraft registration number or train number.
     */
    public function setVehicleNumber(string $vehicleNumber): self
    {
        $this->vehicleNumber = $vehicleNumber;

        return $this;
    }

    /**
     * A brief description of the type of vehicle to board, such as the model and manufacturer of a plane or the class of a boat.
     */
    public function setVehicleType(string $vehicleType): self
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function setFooterImage(Image $image): self
    {
        $this->images['footer'] = $image;

        return $this;
    }

    protected function uncompileSemantics()
    {
        parent::uncompileSemantics();

        $this->boardingGroup = $this->data['semantics']['boardingGroup'] ?? null;
        $this->boardingSequenceNumber = $this->data['semantics']['boardingSequenceNumber'] ?? null;
        $this->confirmationNumber = $this->data['semantics']['confirmationNumber'] ?? null;
        $this->currentArrivalDate = ! empty($this->data['semantics']['currentArrivalDate']) ? Carbon::parse($this->data['semantics']['currentArrivalDate']) : null;
        $this->currentBoardingDate = ! empty($this->data['semantics']['currentBoardingDate']) ? Carbon::parse($this->data['semantics']['currentBoardingDate']) : null;
        $this->currentDepartureDate = ! empty($this->data['semantics']['currentDepartureDate']) ? Carbon::parse($this->data['semantics']['currentDepartureDate']) : null;
        $this->departureLocation = ! empty($this->data['semantics']['departureLocation']) ? Location::fromArray($this->data['semantics']['departureLocation']) : null;
        $this->departureLocationDescription = $this->data['semantics']['departureLocationDescription'] ?? null;
        $this->destinationLocation = ! empty($this->data['semantics']['destinationLocation']) ? Location::fromArray($this->data['semantics']['destinationLocation']) : null;
        $this->destinationLocationDescription = $this->data['semantics']['destinationLocationDescription'] ?? null;
        $this->durationInSeconds = $this->data['semantics']['duration'] ?? null;
        $this->membershipProgramName = $this->data['semantics']['membershipProgramName'] ?? null;
        $this->membershipProgramNumber = $this->data['semantics']['membershipProgramNumber'] ?? null;
        $this->originalArrivalDate = ! empty($this->data['semantics']['originalArrivalDate']) ? Carbon::parse($this->data['semantics']['originalArrivalDate']) : null;
        $this->originalBoardingDate = ! empty($this->data['semantics']['originalBoardingDate']) ? Carbon::parse($this->data['semantics']['originalBoardingDate']) : null;
        $this->originalDepartureDate = ! empty($this->data['semantics']['originalDepartureDate']) ? Carbon::parse($this->data['semantics']['originalDepartureDate']) : null;
        $this->passengerName = ! empty($this->data['semantics']['passengerName']) ? PersonName::fromArray($this->data['semantics']['passengerName']) : null;
        $this->priorityStatus = $this->data['semantics']['priorityStatus'] ?? null;
        $this->seats = ! empty($this->data['semantics']['seats']) ? collect(
            array_map(fn (array $seat) => Seat::fromArray($seat), $this->data['semantics']['seats'])
        ) : null;
        $this->securityScreening = $this->data['semantics']['securityScreening'] ?? null;
        $this->silenceRequested = $this->data['semantics']['silenceRequested'] ?? null;
        $this->transitProvider = $this->data['semantics']['transitProvider'] ?? null;
        $this->transitStatus = $this->data['semantics']['transitStatus'] ?? null;
        $this->transitStatusReason = $this->data['semantics']['transitStatusReason'] ?? null;
        $this->vehicleName = $this->data['semantics']['vehicleName'] ?? null;
        $this->vehicleNumber = $this->data['semantics']['vehicleNumber'] ?? null;
        $this->vehicleType = $this->data['semantics']['vehicleType'] ?? null;
    }

    protected function compileSemantics(): array
    {
        return array_merge(
            parent::compileSemantics(),
            array_filter([
                'boardingGroup' => $this->boardingGroup,
                'boardingSequenceNumber' => $this->boardingSequenceNumber,
                'confirmationNumber' => $this->confirmationNumber,
                'currentArrivalDate' => $this->currentArrivalDate?->toIso8601String(),
                'currentBoardingDate' => $this->currentBoardingDate?->toIso8601String(),
                'currentDepartureDate' => $this->currentDepartureDate?->toIso8601String(),
                'departureLocation' => $this->departureLocation?->toArray(),
                'departureLocationDescription' => $this->departureLocationDescription,
                'destinationLocation' => $this->destinationLocation?->toArray(),
                'destinationLocationDescription' => $this->destinationLocationDescription,
                'duration' => $this->durationInSeconds,
                'membershipProgramName' => $this->membershipProgramName,
                'membershipProgramNumber' => $this->membershipProgramNumber,
                'originalArrivalDate' => $this->originalArrivalDate?->toIso8601String(),
                'originalBoardingDate' => $this->originalBoardingDate?->toIso8601String(),
                'originalDepartureDate' => $this->originalDepartureDate?->toIso8601String(),
                'passengerName' => $this->passengerName?->toArray(),
                'priorityStatus' => $this->priorityStatus,
                'seats' => $this->seats?->toArray(),
                'securityScreening' => $this->securityScreening,
                'silenceRequsted' => $this->silenceRequested,
                'transitProvider' => $this->transitProvider,
                'transitStatus' => $this->transitStatus,
                'transitStatusReason' => $this->transitStatusReason,
                'vehicleName' => $this->vehicleName,
                'vehicleNumber' => $this->vehicleNumber,
                'vehicleType' => $this->vehicleType,
            ]),
        );
    }

    protected function compileData(): array
    {
        return array_merge(
            parent::compileData(),
            [
                'boardingPass' => array_filter([
                    'transitType' => $this->transitType?->value,
                    'primaryFields' => $this->primaryFields?->values()->toArray(),
                    'secondaryFields' => $this->secondaryFields?->values()->toArray(),
                    'headerFields' => $this->headerFields?->values()->toArray(),
                    'auxiliaryFields' => $this->auxiliaryFields?->values()->toArray(),
                    'backFields' => $this->backFields?->values()->toArray(),
                ]),
            ],
        );
    }
}
