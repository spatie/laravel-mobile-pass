<?php

namespace Spatie\LaravelMobilePass\Builders\Apple;

use Illuminate\Support\Carbon;
use Spatie\LaravelMobilePass\Builders\Apple\Concerns\HasSeats;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Location;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\PersonName;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\ApplePassValidator;
use Spatie\LaravelMobilePass\Builders\Apple\Validators\BoardingApplePassValidator;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\TransitType;

abstract class BoardingPassBuilder extends ApplePassBuilder
{
    use HasSeats;

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

    protected ?string $securityScreening = null;

    protected ?bool $silenceRequested = null;

    protected ?string $transitProvider = null;

    protected ?string $transitStatus = null;

    protected ?string $transitStatusReason = null;

    protected ?string $vehicleName = null;

    protected ?string $vehicleNumber = null;

    protected ?string $vehicleType = null;

    protected static function validator(): ApplePassValidator
    {
        return new BoardingApplePassValidator;
    }

    /** A group number for boarding. */
    public function setBoardingGroup(string $boardingGroup): static
    {
        $this->boardingGroup = $boardingGroup;

        return $this;
    }

    /** A sequence number for boarding. */
    public function setBoardingSequenceNumber(string $boardingSequenceNumber): static
    {
        $this->boardingSequenceNumber = $boardingSequenceNumber;

        return $this;
    }

    /** A booking or reservation confirmation number. */
    public function setConfirmationNumber(string $confirmationNumber): static
    {
        $this->confirmationNumber = $confirmationNumber;

        return $this;
    }

    /** The updated date and time of arrival, if different from the originally scheduled date and time. */
    public function setCurrentArrivalDate(Carbon $currentArrivalDate): static
    {
        $this->currentArrivalDate = $currentArrivalDate;

        return $this;
    }

    /** The updated date and time of boarding, if different from the originally scheduled date and time. */
    public function setCurrentBoardingDate(Carbon $currentBoardingDate): static
    {
        $this->currentBoardingDate = $currentBoardingDate;

        return $this;
    }

    /** The updated departure date and time, if different from the originally scheduled date and time. */
    public function setCurrentDepartureDate(Carbon $currentDepartureDate): static
    {
        $this->currentDepartureDate = $currentDepartureDate;

        return $this;
    }

    /** An object that represents the geographic coordinates of the transit departure location, suitable for display on a map. If possible, use precise locations, which are more useful to travelers; for example, the specific location of an airport gate. */
    public function setDepartureLocation(Location $departureLocation): static
    {
        $this->departureLocation = $departureLocation;

        return $this;
    }

    /** A brief description of the departure location. For example, for a flight departing from an airport that has a code of LHR, an appropriate description might be London, Heathrow. */
    public function setDepartureLocationDescription(string $departureLocationDescription): static
    {
        $this->departureLocationDescription = $departureLocationDescription;

        return $this;
    }

    /** An object that represents the geographic coordinates of the transit departure location, suitable for display on a map. */
    public function setDestinationLocation(Location $destinationLocation): static
    {
        $this->destinationLocation = $destinationLocation;

        return $this;
    }

    /** A brief description of the destination location. For example, for a flight arriving at an airport that has a code of MPM, Maputo might be an appropriate description. */
    public function setDestinationLocationDescription(string $destinationLocationDescription): static
    {
        $this->destinationLocationDescription = $destinationLocationDescription;

        return $this;
    }

    /** The duration of the transit journey, in seconds. */
    public function setDuration(int $durationInSeconds): static
    {
        $this->durationInSeconds = $durationInSeconds;

        return $this;
    }

    /** The name of a frequent flyer or loyalty program. */
    public function setMembershipProgramName(string $membershipProgramName): static
    {
        $this->membershipProgramName = $membershipProgramName;

        return $this;
    }

    /** The ticketed passenger’s frequent flyer or loyalty number. */
    public function setMembershipProgramNumber(string $membershipProgramNumber): static
    {
        $this->membershipProgramNumber = $membershipProgramNumber;

        return $this;
    }

    /** The originally scheduled date and time of arrival. */
    public function setOriginalArrivalDate(Carbon $originalArrivalDate): static
    {
        $this->originalArrivalDate = $originalArrivalDate;

        return $this;
    }

    /** The originally scheduled date and time of boarding. */
    public function setOriginalBoardingDate(Carbon $originalBoardingDate): static
    {
        $this->originalBoardingDate = $originalBoardingDate;

        return $this;
    }

    /** The originally scheduled date and time of departure. */
    public function setOriginalDepartureDate(Carbon $originalDepartureDate): static
    {
        $this->originalDepartureDate = $originalDepartureDate;

        return $this;
    }

    /** An object that represents the name of the passenger. */
    public function setPassengerName(PersonName $passengerName): static
    {
        $this->passengerName = $passengerName;

        return $this;
    }

    /** The priority status the ticketed passenger holds, such as “Gold” or “Silver”. */
    public function setPriorityStatus(string $priorityStatus): static
    {
        $this->priorityStatus = $priorityStatus;

        return $this;
    }

    /** The type of security screening for the ticketed passenger, such as “Priority”. */
    public function setSecurityScreening(string $securityScreening): static
    {
        $this->securityScreening = $securityScreening;

        return $this;
    }

    /** A Boolean value that determines whether the user’s device remains silent during a transit journey. The system may override the key and determine the length of the period of silence. */
    public function setSilenceRequested(bool $silenceRequested): static
    {
        $this->silenceRequested = $silenceRequested;

        return $this;
    }

    /** The name of the transit company. */
    public function setTransitProvider(string $transitProvider): static
    {
        $this->transitProvider = $transitProvider;

        return $this;
    }

    /** A brief description of the current boarding status for the vessel, such as “On Time” or “Delayed”. For delayed status, provide currentBoardingDate, currentDepartureDate, and currentArrivalDate where available. */
    public function setTransitStatus(string $transitStatus): static
    {
        $this->transitStatus = $transitStatus;

        return $this;
    }

    /** A brief description that explains the reason for the current transitStatus, such as “Thunderstorms”. */
    public function setTransitStatusReason(string $transitStatusReason): static
    {
        $this->transitStatusReason = $transitStatusReason;

        return $this;
    }

    /** The name of the vehicle to board, such as the name of a boat. */
    public function setVehicleName(string $vehicleName): static
    {
        $this->vehicleName = $vehicleName;

        return $this;
    }

    /** The identifier of the vehicle to board, such as the aircraft registration number or train number. */
    public function setVehicleNumber(string $vehicleNumber): static
    {
        $this->vehicleNumber = $vehicleNumber;

        return $this;
    }

    /** A brief description of the type of vehicle to board, such as the model and manufacturer of a plane or the class of a boat. */
    public function setVehicleType(string $vehicleType): static
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function setFooterImage(string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->images['footer'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setLocaleFooterImage(string $language, string $x1Path, ?string $x2Path = null, ?string $x3Path = null): static
    {
        $this->locales[$language]['images']['footer'] = new Image($x1Path, $x2Path, $x3Path);

        return $this;
    }

    public function setRemoteLocaleFooterImage(string $language, string $x1Url, ?string $x2Url = null, ?string $x3Url = null): static
    {
        $this->locales[$language]['images']['footer'] = Image::makeRemote($x1Url, $x2Url, $x3Url);

        return $this;
    }

    protected function uncompileSemantics(): void
    {
        parent::uncompileSemantics();

        $semantics = $this->data['semantics'] ?? [];

        $this->boardingGroup = $semantics['boardingGroup'] ?? null;
        $this->boardingSequenceNumber = $semantics['boardingSequenceNumber'] ?? null;
        $this->confirmationNumber = $semantics['confirmationNumber'] ?? null;
        $this->currentArrivalDate = $this->parseSemanticDate($semantics, 'currentArrivalDate');
        $this->currentBoardingDate = $this->parseSemanticDate($semantics, 'currentBoardingDate');
        $this->currentDepartureDate = $this->parseSemanticDate($semantics, 'currentDepartureDate');
        $this->departureLocation = empty($semantics['departureLocation'])
            ? null
            : Location::fromArray($semantics['departureLocation']);
        $this->departureLocationDescription = $semantics['departureLocationDescription'] ?? null;
        $this->destinationLocation = empty($semantics['destinationLocation'])
            ? null
            : Location::fromArray($semantics['destinationLocation']);
        $this->destinationLocationDescription = $semantics['destinationLocationDescription'] ?? null;
        $this->durationInSeconds = $semantics['duration'] ?? null;
        $this->membershipProgramName = $semantics['membershipProgramName'] ?? null;
        $this->membershipProgramNumber = $semantics['membershipProgramNumber'] ?? null;
        $this->originalArrivalDate = $this->parseSemanticDate($semantics, 'originalArrivalDate');
        $this->originalBoardingDate = $this->parseSemanticDate($semantics, 'originalBoardingDate');
        $this->originalDepartureDate = $this->parseSemanticDate($semantics, 'originalDepartureDate');
        $this->passengerName = empty($semantics['passengerName'])
            ? null
            : PersonName::fromArray($semantics['passengerName']);
        $this->priorityStatus = $semantics['priorityStatus'] ?? null;
        $this->uncompileSeats($semantics);
        $this->securityScreening = $semantics['securityScreening'] ?? null;
        $this->silenceRequested = $semantics['silenceRequested'] ?? null;
        $this->transitProvider = $semantics['transitProvider'] ?? null;
        $this->transitStatus = $semantics['transitStatus'] ?? null;
        $this->transitStatusReason = $semantics['transitStatusReason'] ?? null;
        $this->vehicleName = $semantics['vehicleName'] ?? null;
        $this->vehicleNumber = $semantics['vehicleNumber'] ?? null;
        $this->vehicleType = $semantics['vehicleType'] ?? null;
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
                'silenceRequested' => $this->silenceRequested,
                'transitProvider' => $this->transitProvider,
                'transitStatus' => $this->transitStatus,
                'transitStatusReason' => $this->transitStatusReason,
                'vehicleName' => $this->vehicleName,
                'vehicleNumber' => $this->vehicleNumber,
                'vehicleType' => $this->vehicleType,
            ], fn ($value) => $value !== null),
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
